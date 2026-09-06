<?php

namespace Modules\Api\Http\Controllers\Voip;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;

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

        $appointments = AppointmentUser::query()
            ->with(['user:id,mobile', 'doctor:id'])
            ->whereHas('user', function ($query) use ($phone) {
                // The last ten digits make local 09..., +989..., and 00989... forms match.
                $query->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(mobile, '+', ''), ' ', ''), '-', ''), '(', '') LIKE ?", ['%'.$phone])
                    ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(mobile, '+', ''), ' ', ''), '-', ''), '(', '') LIKE ?", ['%'.ltrim($phone, '0')]);
            })
            ->whereIn('status', [
                AppointmentUserStatusEnum::STATUS_SUCCESSFUL->value,
            ])
            ->whereNotNull('date_visit')
            ->where('date_visit', '>=', now('Asia/Tehran'))
            ->orderBy('date_visit')
            ->get();

        $today = Carbon::now('Asia/Tehran')->toDateString();
        $extensions = ConsultationPractitioner::whereIn('user_id', $appointments->pluck('doctor_id')->filter())
            ->pluck('extension', 'user_id');
        $items = $appointments->map(function (AppointmentUser $appointment) use ($today, $extensions) {
            $date = Carbon::parse($appointment->date_visit, 'Asia/Tehran');

            return [
                'appointment_code' => $appointment->id,
                'tracking_code' => $appointment->tracking_code,
                'date_visit' => $date->toIso8601String(),
                'is_today' => $date->toDateString() === $today,
                'doctor_id' => $appointment->doctor_id,
                'doctor_extension' => $extensions->get($appointment->doctor_id),
                'status' => $appointment->status->value,
            ];
        })->values();

        return $this->ok([
            'status' => true,
            'error_code' => $items->isNotEmpty() ? VoipResponseCode::SUCCESS : VoipResponseCode::APPOINTMENT_NOT_FOUND,
            'has_appointment' => $items->isNotEmpty(),
            'has_appointment_today' => $items->contains('is_today', true),
            'message' => $items->isNotEmpty() ? 'نوبت آینده وجود دارد.' : 'نوبت آینده‌ای برای این شماره وجود ندارد.',
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

        return ltrim($digits, '0') === '' ? '' : ltrim($digits, '0');
    }
}
