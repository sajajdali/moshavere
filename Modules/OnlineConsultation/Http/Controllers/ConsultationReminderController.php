<?php

namespace Modules\OnlineConsultation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\OnlineConsultation\Models\ConsultationSmsDelivery;
use Modules\OnlineConsultation\Models\ConsultationSmsReminderRule;
use Modules\OnlineConsultation\Services\ConsultationReminderScheduler;

class ConsultationReminderController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'queued', 'retrying', 'sent', 'failed', 'skipped'])],
            'recipient_type' => ['nullable', Rule::in(['patient', 'practitioner'])],
            'appointment' => ['nullable', 'string', 'max:100'],
            'edit' => ['nullable', 'integer', 'min:1'],
        ]);
        $editingRule = isset($filters['edit']) ? ConsultationSmsReminderRule::findOrFail($filters['edit']) : null;
        $rules = ConsultationSmsReminderRule::withCount([
            'deliveries as sent_count' => fn ($query) => $query->where('status', 'sent')
                ->whereHas('appointment', fn ($appointment) => $appointment->where('kind', AppointmentUserKindEnum::VOIP->value)),
        ])->orderBy('recipient_type')->orderByDesc('minutes_before')->get();
        $deliveries = ConsultationSmsDelivery::with(['appointment.user', 'appointment.doctor', 'reminderRule'])
            ->whereNotNull('appointment_id')
            ->whereHas('appointment', fn ($query) => $query->where('kind', AppointmentUserKindEnum::VOIP->value))
            ->where(function ($query) {
                $query->whereNotNull('reminder_rule_id')->orWhere('type', 'like', '%reminder%');
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['recipient_type'] ?? null, fn ($query, $recipient) => $query->where('recipient_type', $recipient))
            ->when($filters['appointment'] ?? null, function ($query, $appointment) {
                $query->whereHas('appointment', fn ($appointmentQuery) => $appointmentQuery
                    ->where('tracking_code', 'like', '%'.$appointment.'%')
                    ->orWhere('id', ctype_digit($appointment) ? (int) $appointment : 0));
            })
            ->latest('scheduled_at')->paginate(30)->withQueryString();

        return view('onlineconsultation::sms-reminders', compact('rules', 'deliveries', 'editingRule', 'filters'));
    }

    public function store(Request $request, ConsultationReminderScheduler $scheduler)
    {
        $rule = ConsultationSmsReminderRule::create($this->validated($request));
        $scheduler->syncRule($rule);

        return redirect()->route('admin.consultation.sms-reminders.index')->with('success', 'یادآوری تلفنی اضافه شد.');
    }

    public function update(Request $request, ConsultationSmsReminderRule $rule, ConsultationReminderScheduler $scheduler)
    {
        $rule->update($this->validated($request));
        $scheduler->syncRule($rule);

        return redirect()->route('admin.consultation.sms-reminders.index')->with('success', 'یادآوری تلفنی و برنامه ارسال‌های آینده به‌روز شد.');
    }

    public function destroy(ConsultationSmsReminderRule $rule)
    {
        $rule->deliveries()->whereIn('status', ['pending', 'queued', 'retrying'])
            ->update(['status' => 'skipped', 'error_message' => 'قانون یادآوری حذف شده است.']);
        $rule->delete();

        return redirect()->route('admin.consultation.sms-reminders.index')->with('success', 'یادآوری حذف شد؛ سوابق ارسال حفظ شدند.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'recipient_type' => ['required', Rule::in(['patient', 'practitioner'])],
            'offset_value' => ['required', 'integer', 'min:1', 'max:10080'],
            'offset_unit' => ['required', Rule::in(['minute', 'hour'])],
            'template' => ['required', 'string', 'max:255'],
            'message_text' => ['nullable', 'string', 'max:2000'],
            'active' => ['nullable', 'boolean'],
        ], [], [
            'title' => 'عنوان', 'recipient_type' => 'گیرنده', 'offset_value' => 'زمان ارسال',
            'offset_unit' => 'واحد زمان', 'template' => 'نام قالب پیامکی',
        ]);
        $minutes = (int) $data['offset_value'] * ($data['offset_unit'] === 'hour' ? 60 : 1);
        if ($minutes > 10080) {
            throw ValidationException::withMessages(['offset_value' => 'زمان یادآوری نمی‌تواند بیشتر از ۷ روز باشد.']);
        }

        return [
            'title' => trim($data['title']),
            'recipient_type' => $data['recipient_type'],
            'minutes_before' => $minutes,
            'template' => trim($data['template']),
            'message_text' => filled($data['message_text'] ?? null) ? trim($data['message_text']) : null,
            'active' => $request->boolean('active'),
        ];
    }
}
