<?php

namespace Modules\OnlineConsultation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Services\ConsultantDashboardService;

class ConsultantDashboardController extends Controller
{
    public function index(Request $request, ConsultantDashboardService $service)
    {
        $filters = $request->validate([
            'mode' => ['nullable', 'in:daily,monthly'], 'date' => ['nullable', 'string', 'max:10'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'], 'year' => ['nullable', 'integer', 'min:1300', 'max:1600'],
        ]);
        $mode = $filters['mode'] ?? 'daily';
        $selectedDate = $filters['date'] ?? verta()->format('Y/m/d');
        $selectedYear = (int) ($filters['year'] ?? verta()->format('Y'));
        $selectedMonthNumber = (int) ($filters['month'] ?? verta()->format('m'));
        $selectedMonth = sprintf('%04d/%02d', $selectedYear, $selectedMonthNumber);
        [$from, $to] = $mode === 'monthly' ? $service->monthPeriod($selectedMonth) : $service->dayPeriod($selectedDate);
        $monthOptions = $service->monthOptions();
        $monthNames = $service->monthNames();
        $years = range((int) verta()->format('Y') + 1, (int) verta()->format('Y') - 5);
        $previousDate = verta($from->copy()->subDay())->format('Y/m/d');
        $nextDate = verta($from->copy()->addDay())->format('Y/m/d');
        $practitioners = ConsultationPractitioner::with('user')->where('active', true)->orderBy('display_name')->get();
        $appointments = $service->query($from, $to)->with(['callLogs', 'billingRecord.adjustments', 'billingRecord.approver', 'consultationCase'])->get()->groupBy('doctor_id');
        $rows = $practitioners->map(fn ($profile) => ['profile' => $profile, 'stats' => $service->stats($appointments->get($profile->user_id, collect()))]);

        return view('onlineconsultation::consultant-dashboard.index', compact('rows', 'mode', 'selectedDate', 'selectedMonth', 'selectedYear', 'selectedMonthNumber', 'monthOptions', 'monthNames', 'years', 'previousDate', 'nextDate', 'from', 'to'));
    }

    public function show(Request $request, ConsultationPractitioner $practitioner, ConsultantDashboardService $service)
    {
        $filters = $request->validate([
            'period' => ['nullable', 'in:today,yesterday,week,month,custom'], 'month' => ['nullable', 'regex:/^\d{4}\/\d{2}$/'], 'from' => ['nullable', 'string', 'max:10'], 'to' => ['nullable', 'string', 'max:10'],
            'search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', 'string', 'max:40'], 'call_status' => ['nullable', 'in:answered,unanswered'],
            'missed' => ['nullable', 'boolean'], 'remaining' => ['nullable', 'boolean'], 'has_calls' => ['nullable', 'boolean'],
            'no_successful_call' => ['nullable', 'boolean'], 'refundable' => ['nullable', 'boolean'], 'unsettled' => ['nullable', 'boolean'],
        ]);
        $filters['period'] = $filters['period'] ?? 'today';
        $filters['month'] = $filters['month'] ?? verta()->format('Y/m');
        [$from, $to] = $service->period($filters['period'], $filters['period'] === 'month' ? $filters['month'] : ($filters['from'] ?? null), $filters['to'] ?? null);
        $monthOptions = $service->monthOptions();
        $all = $service->query($from, $to, $practitioner->user_id)->with(['user', 'doctor', 'callLogs', 'billingRecord.adjustments', 'billingRecord.approver', 'consultationCase'])->latest('date_visit')->get()->map(fn ($a) => $service->decorate($a));
        $stats = $service->stats($all);
        $items = $all->filter(function ($a) use ($filters) {
            $d = $a->dashboard;
            $search = trim($filters['search'] ?? '');
            if ($search !== '' && ! str_contains(mb_strtolower(($a->tracking_code ?? '').' '.($a->user?->fullName ?? '').' '.($a->user?->mobile ?? '')), mb_strtolower($search))) {
                return false;
            }
            if (filled($filters['status'] ?? null) && $d['status'] !== $filters['status']) {
                return false;
            }
            if (($filters['call_status'] ?? null) === 'answered' && $d['answered'] < 1) {
                return false;
            }
            if (($filters['call_status'] ?? null) === 'unanswered' && $d['unanswered'] < 1) {
                return false;
            }
            if (($filters['missed'] ?? false) && ! $d['alert']) {
                return false;
            }
            if (($filters['remaining'] ?? false) && ! in_array($d['status'], ['pending', 'in_progress'], true)) {
                return false;
            }
            if (($filters['has_calls'] ?? false) && $d['calls'] < 1) {
                return false;
            }
            if (($filters['no_successful_call'] ?? false) && $d['answered'] > 0) {
                return false;
            }
            if (($filters['refundable'] ?? false) && $d['financial'] !== 'refundable') {
                return false;
            }
            if (($filters['unsettled'] ?? false) && $d['financial'] === 'settled') {
                return false;
            }

            return true;
        });
        if ($request->boolean('export')) {
            return $this->csv($items, $practitioner->display_name);
        }

        return view('onlineconsultation::consultant-dashboard.show', compact('practitioner', 'items', 'stats', 'filters', 'from', 'to', 'monthOptions'));
    }

    public function export(Request $request, ConsultantDashboardService $service)
    {
        $period = $request->input('period', 'today');
        [$from, $to] = $service->period($period, $period === 'month' ? $request->input('month') : $request->input('from'), $request->input('to'));
        $items = $service->query($from, $to)->with(['user', 'doctor', 'callLogs', 'billingRecord.adjustments', 'consultationCase'])->get()->map(fn ($a) => $service->decorate($a));

        return $this->csv($items, 'مرکز');
    }

    private function csv($items, string $name)
    {
        return response()->streamDownload(function () use ($items) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['کد نوبت', 'مشاور', 'بیمار', 'موبایل', 'زمان نوبت', 'وضعیت', 'علت', 'تماس بیمار', 'تماس مشاور', 'پاسخ', 'بی‌پاسخ در زمان نوبت', 'تماس زودهنگام', 'مکالمه (ثانیه)', 'پرداخت', 'بازگشت']);
            foreach ($items as $a) {
                fputcsv($out, [$a->tracking_code ?: $a->id, $a->doctor?->fullName, $a->user?->fullName, $a->user?->mobile, $a->date_visit, $a->dashboard['status'], $a->dashboard['reason'], $a->dashboard['patient_attempts'], $a->dashboard['practitioner_attempts'], $a->dashboard['answered'], $a->dashboard['unanswered'], $a->dashboard['early_calls'], $a->dashboard['talk_seconds'], $a->billingRecord?->total_paid_amount, $a->billingRecord?->refunded_amount]);
            }
            fclose($out);
        }, 'consultation-report-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
