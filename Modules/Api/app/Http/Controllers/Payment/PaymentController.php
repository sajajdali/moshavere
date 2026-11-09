<?php

namespace Modules\Api\App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\Front\Services\AppointmentGatewayPayment;

/**
 * پرداخت نوبت در قالب جدید (اپ React).
 * منطق اصلی (جلوگیری از درخواست تکراری، تایید و ثبت پرداخت) در AppointmentGatewayPayment است.
 */
class PaymentController extends Controller
{
    use ApiHandlerTrait;

    public function __construct(private readonly AppointmentGatewayPayment $payments)
    {
    }

    public function createPaymentLink(AppointmentUser $appointmentUser)
    {
        $amount = (int) data_get(
            $appointmentUser->details,
            AppointmentUser::DETAIL_PAYMENT.'.'.AppointmentUser::DETAIL_PAYMENT_PRICE.'.int',
            0
        );
        $callbackUrl = route('api.appointment.payment.callback', ['appointmentUser' => $appointmentUser->id]);

        $result = $this->payments->start($appointmentUser, $amount, $callbackUrl);

        if (! $result['status']) {
            return $this->toDetail($appointmentUser, ($result['already_paid'] ?? false) ? 'success' : 'failed', $result['message']);
        }

        return redirect()->to($result['url']);
    }

    /**
     * بازگشت از بانک (سداد و سامان با POST، بقیه با GET).
     * شناسه خام گرفته میشود تا نوبتی که در این فاصله حذف نرم شده هم پیدا شود.
     */
    public function callback(string $appointmentUser, Request $request)
    {
        $appointment = AppointmentUser::withTrashed()->find((int) $appointmentUser);
        abort_unless($appointment, 404);

        $result = $this->payments->complete($appointment->id, $request);

        return $this->toDetail($appointment, $result['status'], $result['message']);
    }

    private function toDetail(AppointmentUser $appointment, string $status, string $message)
    {
        return redirect()->route('front.setAppointment.detail', [
            'tracking_code' => $appointment->tracking_code,
            'payment' => $status,
            'msg' => $message,
        ]);
    }
}
