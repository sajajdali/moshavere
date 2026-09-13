<?php

namespace Modules\OnlineConsultation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\OnlineConsultation\Models\AppointmentAlternatePhone;
use Modules\OnlineConsultation\Support\ConsultationAccess;

class AppointmentAlternatePhoneController extends Controller
{
    public function store(Request $request, AppointmentUser $appointment)
    {
        $this->authorizeMutation($request, $appointment);
        if (! ConsultationAccess::schemaReady(['appointment_alternate_phones'])) {
            throw ValidationException::withMessages([
                'alternate_phone' => 'امکان ثبت شماره ثابت هنوز در این سامانه فعال نشده است؛ migration دیتابیس باید اجرا شود.',
            ]);
        }
        $phone = convert2english(trim((string) $request->input('alternate_phone')));
        $request->merge(['alternate_phone' => $phone]);
        $request->validate([
            'alternate_phone' => ['required', 'digits:11', 'regex:/^0[1-8][0-9]{9}$/'],
        ], [
            'alternate_phone.required' => 'شماره تلفن ثابت را وارد کنید.',
            'alternate_phone.digits' => 'شماره ثابت باید دقیقاً ۱۱ رقم و همراه با پیش‌شماره استان باشد.',
            'alternate_phone.regex' => 'فقط شماره ثابت ۱۱ رقمی با پیش‌شماره استان مجاز است؛ شماره موبایل پذیرفته نمی‌شود.',
        ]);

        try {
            DB::transaction(function () use ($appointment, $phone, $request) {
                if (AppointmentAlternatePhone::where('phone', $phone)->lockForUpdate()->exists()) {
                    throw ValidationException::withMessages([
                        'alternate_phone' => 'این شماره قبلاً برای فرد یا نوبت دیگری ثبت شده است.',
                    ]);
                }
                $alternatePhone = AppointmentAlternatePhone::create([
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->user_id,
                    'phone' => $phone,
                    'created_by' => $request->user()->id,
                ]);
                $alternatePhone->setRelation('creator', $request->user());
                return $alternatePhone;
            });
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
                throw ValidationException::withMessages([
                    'alternate_phone' => 'این شماره قبلاً برای فرد یا نوبت دیگری ثبت شده است.',
                ]);
            }
            throw $exception;
        }

        $alternatePhone = AppointmentAlternatePhone::with('creator')->where('phone', $phone)->firstOrFail();
        $message = 'شماره ثابت به حساب بیمار اضافه شد و برای همه نوبت‌های او در VoIP قابل شناسایی است.';
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'phone' => [
                    'id' => $alternatePhone->id,
                    'number' => $alternatePhone->phone,
                    'creator' => $alternatePhone->creator?->fullName ?: '—',
                    'created_at' => verta($alternatePhone->created_at)->format('Y/m/d H:i'),
                    'delete_url' => route('admin.consultation.alternate-phones.destroy', [$appointment, $alternatePhone]),
                ],
            ], 201);
        }

        return back()->with('success', $message);
    }

    public function destroy(Request $request, AppointmentUser $appointment, AppointmentAlternatePhone $alternatePhone)
    {
        $this->authorizeMutation($request, $appointment);
        abort_unless((int) $alternatePhone->patient_id === (int) $appointment->user_id, 404);
        $alternatePhone->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'شماره ثابت از حساب بیمار حذف شد.']);
        }

        return back()->with('success', 'شماره ثابت از حساب بیمار حذف شد.');
    }

    private function authorizeMutation(Request $request, AppointmentUser $appointment): void
    {
        abort_if($appointment->trashed(), 403, 'نوبت حذف‌شده قابل ویرایش نیست.');
        abort_unless(
            (int) $request->user()->id === (int) $appointment->doctor_id || $request->user()->can('SUPER_ADMIN'),
            403,
            'ثبت شماره فقط توسط مشاور این نوبت یا مدیر کل امکان‌پذیر است.'
        );
    }
}
