<?php

namespace Modules\Api\app\Resources\Api\Appointments;

use Carbon\Carbon;
use Modules\Api\Transformers\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;
use Modules\Api\app\Resources\Transaction\TransactionResource;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageTypeEnum;

class AppointmentUserResource extends JsonResource
{
    private function apiResultUser($user)
    {
        return [
            'user_id' => $user?->id,
            'name' => $user?->name
        ];
    }

    private function getServiceName()
    {
        return [
            'id' => $this->service->id ?? null,
            'title' => $this->service->title ?? null,
        ];
    }
    private function getPlaceName()
    {
        return [
            'id' => $this->place->id ?? null,
            'title' => $this->place->title ?? null,
        ];
    }

    private function lastTransaction()
    {
        return TransactionResource::make($this->transaction()->orderByDesc('id')->first());
    }

    private function online()
    {
        if ($this->kind != AppointmentUserKindEnum::ONLINE) {
            return null;
        }
        $online = $this->online->filter(function($c) {
            return !in_array($c->status, [
                AppointmentOnlineStatusEnum::CANCEL,
                AppointmentOnlineStatusEnum::REJECT,
            ]);
        })->sortBy('created_at')->first();

        if (!$online){
            return null;
        }

        return [
            'online_id' => $online->id,
            'online_tracking' => $online->tracking_code,
            'status' => $online->status->apiResult(),
            'new_messages' => (int) $online->new_messages,
            'accessibility' => [
                'can_send_message' => $online->status->canSendMessage(),
                'can_show_messages' => $online->status->canShowMessages(),
            ]
        ];
    }
    /**
     * Transform the resource into an array.
     */

    private function getBadge()
    {
        if ($this->kind == AppointmentUserKindEnum::IN_PERSION) {
            return null;
        }
        $badge = $this->online->first()->messages()->where('type', AppointmentOnlineMessageTypeEnum::ANSWER)->where('seen', '0')->count();
        return $badge > 0 ? $badge . "  پیغام جدید" : null;
    }
    public function toArray($request): array
    {
        $someoneName = null;
        if (isset($this->details[AppointmentUser::DETAIL_SOMEONE])) {
            $firstName = $this->details[AppointmentUser::DETAIL_SOMEONE]['first_name'];
            $lastName = $this->details[AppointmentUser::DETAIL_SOMEONE]['last_name'];
            $someoneName = "($firstName $lastName)";
        }
        $result = [
            'id' => $this->id,
            'service' => $this->getServiceName(),
            'badge' => $this->getBadge(),
            'main_user' => UserResource::make($this->user, $someoneName),
            'for_himself' => !isset($this->details[AppointmentUser::DETAIL_FOR_HIMSELF]) || $this->details[AppointmentUser::DETAIL_FOR_HIMSELF] == 1,
            'someone' => $this->details[AppointmentUser::DETAIL_SOMEONE] ?? null,
            'place' => $this->getPlaceName(),
            'tracking_code' => $this->tracking_code,
            'status' => $this->status->apiResult(),
            'type' => $this->type->apiResult(),
            'kind' => $this->kind->apiResult(),
            'start_time' => substr($this->start_time, 0, -3),
            'end_time' => substr($this->end_time, 0, -3),
            'date_visit' => verta($this->date_visit)->format('%d %B %Y'),
            'date_visit_format' => verta($this->date_visit)->format('l j F Y'),
            'doctor'    => DoctorResource::make($this->doctor),
            'deadline_payment' => $this->deadline_at ? Carbon::parse($this->deadline_at)->diffForHumans() : null,
            'timestamp' => Carbon::parse($this->date_visit)->timestamp,
            'transaction' => $this->lastTransaction(),
            'payment_status' => $this->details[AppointmentUser::DETAIL_PAYMENT] ?? null,
            'payment_link' =>  route('api.appointment.payment.create', $this),
            'tracking_url' => route('front.setAppointment.detail', ['tracking_code' => $this->tracking_code]),
            'location_link' => 'https://www.google.com/maps/place/Dr+Mehrnoush+Amiri+Siyavashani/@35.7989335,51.4732843,15z/data=!4m2!3m1!1s0x0:0xd520695f679116d1?sa=X&ved=1t:2428&ictx=111',
            'online' => $this->online() ,

        ];
        if ($this->kind == AppointmentUserKindEnum::IN_PERSION) {
            $result['message'] =[
                'type' => 'warning' ,
                'title' => 'نکته مهم',
                'body' => 'کاربر محترم، توجه داشته باشید که در ساعت مورد نظر که اعلام شده است حضور داشته باشید. در صورت عدم حضور به‌موقع نوبت شما لغو می‌گردد.'
            ];
        } else {
            $result['message'] = null;
        }
        return $result;
    }
}
