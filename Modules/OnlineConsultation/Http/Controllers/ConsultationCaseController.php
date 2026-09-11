<?php

namespace Modules\OnlineConsultation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Hekmatinasser\Verta\Verta;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\OnlineConsultation\Models\AppointmentConsultationReport;
use Modules\OnlineConsultation\Services\ConsultationCaseService;

class ConsultationCaseController extends Controller
{
    public function storeReport(Request $request, AppointmentUser $appointment, ConsultationCaseService $service)
    {
        $this->authorizeMutation($request, $appointment);
        $data = $request->validate([
            'outcome' => ['required', Rule::in(array_keys(AppointmentConsultationReport::OUTCOMES))],
            'subject' => ['required', 'string', 'min:3', 'max:200'],
            'report_text' => ['required', 'string', 'min:10', 'max:10000'],
            'follow_up_date' => ['nullable', 'required_without:follow_up_at', 'required_with:follow_up_time', 'regex:/^\d{4}\/\d{1,2}\/\d{1,2}$/'],
            'follow_up_time' => ['nullable', 'required_without:follow_up_at', 'required_with:follow_up_date', 'date_format:H:i'],
            // Backward compatibility for API clients that still send a Gregorian datetime.
            'follow_up_at' => ['nullable', 'required_without_all:follow_up_date,follow_up_time', 'date'],
        ], [
            'follow_up_date.required_without' => 'تاریخ گزارش الزامی است.',
            'follow_up_date.required_with' => 'تاریخ گزارش الزامی است.',
            'follow_up_date.regex' => 'تاریخ گزارش باید یک تاریخ شمسی معتبر باشد.',
            'follow_up_time.required_without' => 'ساعت گزارش الزامی است.',
            'follow_up_time.required_with' => 'ساعت گزارش الزامی است.',
            'follow_up_time.date_format' => 'ساعت گزارش معتبر نیست.',
            'follow_up_at.required_without_all' => 'تاریخ و ساعت گزارش الزامی است.',
        ]);
        if (filled($data['follow_up_date'] ?? null)) {
            try {
                [$hour, $minute] = array_map('intval', explode(':', $data['follow_up_time']));
                $data['follow_up_at'] = Verta::parse($data['follow_up_date'])->toCarbon()->setTime($hour, $minute);
            } catch (\Throwable) {
                return back()->withErrors(['follow_up_date' => 'تاریخ شمسی گزارش معتبر نیست.'])->withInput();
            }
        }
        $service->addReport($appointment, $request->user(), $data);

        return back()->with('success', 'گزارش مشاوره با تاریخ و نام ثبت‌کننده ذخیره شد.');
    }

    public function storeNote(Request $request, AppointmentUser $appointment, ConsultationCaseService $service)
    {
        $this->authorizeMutation($request, $appointment);
        $data = $request->validate([
            'appointment_note' => ['required', 'string', 'min:3', 'max:5000'],
        ]);
        $service->addAppointmentNote($appointment, $request->user(), $data['appointment_note']);

        return back()->with('success', 'توضیحات این نوبت همراه با نام و نقش ثبت‌کننده ذخیره شد.');
    }

    public function complete(Request $request, AppointmentUser $appointment, ConsultationCaseService $service)
    {
        $this->authorizeMutation($request, $appointment);
        $request->validate(['completion_confirmed' => ['accepted']]);
        $service->complete($appointment, $request->user());

        return back()->with('success', 'مشاوره تمام شد و اتصال مجدد از طریق VoIP مسدود شد.');
    }

    public function patientNoShow(Request $request, AppointmentUser $appointment, ConsultationCaseService $service)
    {
        $this->authorizeMutation($request, $appointment);
        $request->validate(['no_show_confirmed' => ['accepted']]);
        $service->markPatientNoShow($appointment, $request->user());
        return back()->with('success', 'عدم حضور بیمار ثبت و کل مبلغ نوبت تسویه شد؛ بازگشت به کیف پول صفر است.');
    }

    public function reopen(Request $request, AppointmentUser $appointment, ConsultationCaseService $service)
    {
        $this->authorizeMutation($request, $appointment);
        $data = $request->validate([
            'reopen_reason' => ['required', 'string', 'min:5', 'max:2000'],
            'reopen_confirmed' => ['accepted'],
        ]);
        $service->reopen($appointment, $request->user(), $data['reopen_reason']);

        return back()->with('success', 'پرونده مشاوره باز شد و دلیل بازگشایی در سابقه ثبت گردید.');
    }

    private function authorizeMutation(Request $request, AppointmentUser $appointment): void
    {
        abort_unless(
            (int) $request->user()->id === (int) $appointment->doctor_id || $request->user()->can('SUPER_ADMIN'),
            403,
            'ثبت یا تغییر پرونده فقط توسط مشاور این نوبت یا مدیر کل امکان‌پذیر است.'
        );
    }
}
