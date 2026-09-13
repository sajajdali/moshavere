<?php

namespace Modules\Api\Http\Controllers\Voip;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;

/**
 * Endpoints used by the VoIP integration are kept separate from booking APIs.
 */
class AppointmentStatusController extends Controller
{
    use ApiHandlerTrait;

    /**
     * Check whether a caller has a confirmed future appointment.
     *
     * Query: phone=09120000000 (mobile or landline, as stored on the patient).
     */
    public function show(Request $request)
    {
        $validator = validator($request->all(), [
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\-\s]+$/'],
        ]);
        if ($validator->fails()) {
            return $this->badRequest([
                'status' => false,
                'error_code' => VoipResponseCode::INVALID_PHONE,
                'message' => 'شماره موبایل یا تلفن ثابت معتبر نیست.',
                'errors' => $validator->errors()->toArray(),
            ]);
        }
        $data = $validator->validated();

        $phone = $this->normalizePhone($data['phone']);
        if ($phone === '') {
            return $this->badRequest([
                'status' => false,
                'error_code' => VoipResponseCode::INVALID_PHONE,
                'message' => 'شماره موبایل یا تلفن ثابت معتبر نیست.',
            ]);
        }

        $now = Carbon::now('Asia/Tehran');
        $today = $now->toDateString();
        $ignoredShortCallMinutes = max(0, (int) ConsultationSetting::current()->ignored_short_call_minutes);
        $ignoredShortCallSeconds = $ignoredShortCallMinutes * 60;
        $alternatePatientId = $this->alternatePhonePatientId($phone);

        $appointments = AppointmentUser::query()
            ->with(['user:id,mobile', 'doctor:id,mobile', 'consultationCase'])
            ->where(function ($query) use ($phone, $alternatePatientId) {
                $query->whereHas('user', function ($query) use ($phone) {
                    // The last ten digits make local 09..., +989..., and 00989... forms match.
                    $query->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(mobile, '+', ''), ' ', ''), '-', ''), '(', '') LIKE ?", ['%'.$phone])
                        ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(mobile, '+', ''), ' ', ''), '-', ''), '(', '') LIKE ?", ['%'.ltrim($phone, '0')]);
                });
                if ($alternatePatientId !== null) {
                    $query->orWhere('user_id', $alternatePatientId);
                }
            })
            ->whereIn('status', [
                AppointmentUserStatusEnum::STATUS_SUCCESSFUL->value,
            ])
            ->whereNotNull('date_visit')
            ->whereDate('date_visit', '>=', $today)
            ->orderBy('date_visit')
            ->get();

        $extensions = ConsultationPractitioner::whereIn('user_id', $appointments->pluck('doctor_id')->filter())
            ->pluck('extension', 'user_id');
        $items = $appointments->map(function (AppointmentUser $appointment) use ($today, $extensions, $now, $ignoredShortCallMinutes, $ignoredShortCallSeconds) {
            $date = Carbon::parse($appointment->date_visit, 'Asia/Tehran');
            $start = $date->copy();
            $end = $date->copy()->setTimeFromTimeString($appointment->end_time ?: $date->copy()->addMinutes(30)->format('H:i:s'));
            if ($end->lt($start)) {
                $end->addDay();
            }
            if ($end->lt($now)) {
                return null;
            }
            $inWindow = $now->betweenIncluded($start, $end);
            $consultationCompleted = $appointment->consultationCase?->isClosed() ?? false;

            $item = [
                'appointment_id' => $appointment->id,
                'appointment_code' => $appointment->id,
                'tracking_code' => $appointment->tracking_code,
                'date_visit' => $date->toIso8601String(),
                'start_time' => $start->format('H:i:s'),
                'end_time' => $end->format('H:i:s'),
                'is_today' => $date->toDateString() === $today,
                'is_time_for_appointment' => $inWindow,
                'can_connect' => $inWindow && ! $consultationCompleted,
                'consultation_completed' => $consultationCompleted,
                'consultation_status' => $appointment->consultationCase?->state ?? 'OPEN',
                'consultation_completed_at' => $appointment->consultationCase?->completed_at?->toIso8601String(),
                'ignored_short_call_minutes' => $ignoredShortCallMinutes,
                'ignored_short_call_seconds' => $ignoredShortCallSeconds,
                'doctor_id' => $appointment->doctor_id,
                'doctor_extension' => $extensions->get($appointment->doctor_id),
                'status' => $appointment->status->value,
            ];
            // The practitioner's private mobile is exposed only during the active
            // appointment window, so VoIP can fall back to it if the extension fails.
            if ($inWindow && ! $consultationCompleted) {
                $item['doctor_mobile'] = $appointment->doctor?->mobile;
            }

            return $item;
        })->filter()->values();

        // VoIP can use the top-level value directly. When more than one appointment
        // exists, prefer the currently connectable one, then the nearest future one.
        $selectedAppointment = $items->firstWhere('can_connect', true) ?? $items->first();
        $selectedCompleted = (bool) ($selectedAppointment['consultation_completed'] ?? false);
        $responseCode = $items->isEmpty()
            ? VoipResponseCode::APPOINTMENT_NOT_FOUND
            : ($selectedCompleted ? VoipResponseCode::CONSULTATION_COMPLETED : VoipResponseCode::SUCCESS);

        return $this->ok([
            'status' => true,
            'error_code' => $responseCode,
            'has_appointment' => $items->isNotEmpty(),
            'has_appointment_today' => $items->contains('is_today', true),
            'is_time_for_appointment' => $items->contains('is_time_for_appointment', true),
            'can_connect' => $items->contains('can_connect', true),
            'consultation_completed' => $selectedCompleted,
            'consultation_status' => $selectedAppointment['consultation_status'] ?? null,
            'consultation_completed_at' => $selectedAppointment['consultation_completed_at'] ?? null,
            'appointment_id' => $selectedAppointment['appointment_id'] ?? null,
            'doctor_extension' => $selectedAppointment['doctor_extension'] ?? null,
            'doctor_mobile' => ($selectedAppointment['can_connect'] ?? false) ? ($selectedAppointment['doctor_mobile'] ?? null) : null,
            'ignored_short_call_minutes' => $ignoredShortCallMinutes,
            'ignored_short_call_seconds' => $ignoredShortCallSeconds,
            'message' => $selectedCompleted
                ? 'مشاوره تمام شده است و اتصال مجدد مجاز نیست.'
                : ($items->isNotEmpty() ? 'نوبت آینده وجود دارد.' : 'نوبت آینده‌ای برای این شماره وجود ندارد.'),
            'appointments' => $items,
        ]);
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($digits, '0098')) {
            $digits = substr($digits, 4);
        } elseif (str_starts_with($digits, '98')) {
            $digits = substr($digits, 2);
        }
        // Tehran landlines arrive from the PBX as the eight-digit subscriber
        // number. Restore the province prefix before doing an exact lookup.
        if (strlen($digits) === 8) {
            $digits = '021'.$digits;
        }

        return ltrim($digits, '0') === '' ? '' : ltrim($digits, '0');
    }

    private function alternatePhonePatientId(string $phone): ?int
    {
        if (! Schema::hasTable('appointment_alternate_phones')) return null;

        $query = \Modules\OnlineConsultation\Models\AppointmentAlternatePhone::query();
        if (strlen($phone) !== 10) {
            return null;
        }
        $query->where('phone', '0'.$phone);

        $patientIds = $query->pluck('patient_id')->unique()->values();

        return $patientIds->count() === 1 ? (int) $patientIds->first() : null;
    }
}
