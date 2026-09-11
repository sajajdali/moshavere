<?php

namespace Modules\OnlineConsultation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Services\ConsultantDashboardService;
use Modules\OnlineConsultation\Services\ConsultantFinancialReportService;

class ConsultantFinancialReportController extends Controller
{
    public function index(Request $request, ConsultantFinancialReportService $service, ConsultantDashboardService $dashboard)
    {
        $filters = $request->validate([
            'period' => ['nullable', 'in:today,yesterday,week,month,custom'],
            'month' => ['nullable', 'regex:/^\d{4}\/\d{2}$/'],
            'from' => ['nullable', 'string', 'max:10'],
            'to' => ['nullable', 'string', 'max:10'],
            'practitioner_id' => ['nullable', 'integer', Rule::exists('consultation_practitioners', 'id')],
            'settlement_status' => ['nullable', 'in:all,finalized,pending'],
        ]);
        $filters['period'] = $filters['period'] ?? 'month';
        $filters['month'] = $filters['month'] ?? verta()->format('Y/m');
        $filters['settlement_status'] = $filters['settlement_status'] ?? 'all';
        $profileId = isset($filters['practitioner_id']) ? (int) $filters['practitioner_id'] : null;
        [$from, $to] = $service->period($filters['period'], $filters['month'], $filters['from'] ?? null, $filters['to'] ?? null);
        $report = $service->report($from, $to, $profileId, $filters['settlement_status']);
        $practitioners = ConsultationPractitioner::with('user')->orderByDesc('active')->orderBy('display_name')->get();
        $monthOptions = $dashboard->monthOptions(36);

        if ($request->boolean('export')) {
            return $this->csv($report, $from, $to);
        }

        return view('onlineconsultation::financial-report.index', compact('filters', 'from', 'to', 'report', 'practitioners', 'monthOptions'));
    }

    private function csv(array $report, $from, $to)
    {
        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            $financialHeader = ['عدم حضور بیمار (تسویه کامل)', 'مبلغ نهایی‌شده', 'مبلغ در انتظار تأیید', 'بازگشت قطعی', 'بازگشت پیشنهادی (غیرقطعی)', 'کل هزینه برگشتی قطعی', 'خالص قطعی', 'سهم قطعی کارشناس', 'سود قطعی مجموعه'];
            $financialValues = fn (array $m) => [$m['patient_no_show'], $m['gross_income'], $m['pending_gross_income'], $m['refunded'], $m['pending_refund'], $m['effective_refund'], $m['net_after_refund'], $m['practitioner_income'], $m['platform_profit']];

            fputcsv($out, ['جمع کل گزارش']);
            fputcsv($out, array_merge(['کل نوبت', 'بیمار منحصربه‌فرد', 'کل تماس', 'بی‌پاسخ در زمان نوبت', 'تماس زودهنگام'], $financialHeader));
            fputcsv($out, array_merge([$report['allMetrics']['appointments'], $report['allMetrics']['unique_patients'], $report['allMetrics']['calls'], $report['allMetrics']['unanswered'], $report['allMetrics']['early_calls']], $financialValues($report['allMetrics'])));
            fputcsv($out, []);
            fputcsv($out, ['تفکیک کارشناسان']);
            fputcsv($out, array_merge(['کارشناس', 'نوبت', 'بیمار', 'ورودی', 'خروجی', 'پاسخ', 'بی‌پاسخ در زمان نوبت', 'تماس زودهنگام', 'قطع توسط کارشناس', 'عدم پاسخ کارشناس', 'دایورت', 'دقایق خام', 'دقایق حذف‌شده', 'دقایق مالی'], $financialHeader, ['تعداد نظرسنجی', 'میانگین رضایت']));
            foreach ($report['rows'] as $row) {
                $m = $row['metrics'];
                fputcsv($out, array_merge([$row['profile']->display_name, $m['appointments'], $m['unique_patients'], $m['inbound'], $m['outbound'], $m['answered'], $m['unanswered'], $m['early_calls'], $m['consultant_hangups'], $m['consultant_no_answers'], $m['diverted'], (int) ceil($m['raw_talk_seconds'] / 60), (int) ceil($m['ignored_talk_seconds'] / 60), $m['talk_minutes']], $financialValues($m), [$m['survey_appointments'], $m['survey_average']]));
            }
            fputcsv($out, []);
            fputcsv($out, ['روند روزانه تفکیکی']);
            fputcsv($out, array_merge(['تاریخ', 'نوبت', 'تماس', 'بی‌پاسخ در زمان نوبت', 'تماس زودهنگام', 'دقایق مالی'], $financialHeader));
            foreach ($report['daily'] as $day) {
                $m = $day['metrics'];
                fputcsv($out, array_merge([$day['label'], $m['appointments'], $m['calls'], $m['unanswered'], $m['early_calls'], $m['talk_minutes']], $financialValues($m)));
            }
            fputcsv($out, []);
            fputcsv($out, ['ریز نوبت‌ها']);
            fputcsv($out, array_merge(['شناسه نوبت', 'کد پیگیری', 'تاریخ', 'کارشناس', 'بیمار', 'وضعیت تسویه', 'بی‌پاسخ در زمان نوبت', 'تماس زودهنگام', 'دقایق مالی'], $financialHeader));
            foreach ($report['appointmentRows'] as $row) {
                $appointment = $row['appointment'];
                $m = $row['metrics'];
                fputcsv($out, array_merge([$appointment->id, $appointment->tracking_code, $appointment->date_visit ? verta($appointment->date_visit)->format('Y/m/d H:i') : '', $appointment->doctor?->fullName, $appointment->user?->fullName, $m['settled'] ? 'تأیید و نهایی‌شده' : 'نیازمند تأیید نهایی', $m['unanswered'], $m['early_calls'], $m['talk_minutes']], $financialValues($m)));
            }
            fclose($out);
        }, 'consultant-financial-report-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
