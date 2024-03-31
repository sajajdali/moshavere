<?php

namespace Modules\Api\app\Resources\Api\Appointments;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Api\app\Resources\Transaction\TransactionResource;
use Modules\Api\Transformers\UserResource;
use Modules\AppointmentUser\app\Models\AppointmentUser;

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
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'service' => $this->getServiceName(),
            'main_user' => UserResource::make($this->user),
            'for_himself' => !isset($this->details[AppointmentUser::DETAIL_FOR_HIMSELF]) || $this->details[AppointmentUser::DETAIL_FOR_HIMSELF] == 1,
            'someone' => $this->details[AppointmentUser::DETAIL_SOMEONE] ?? null,
            'place' => $this->getPlaceName(),
            'tracking_code' => $this->tracking_code,
            'status' => $this->status->apiResult(),
            'type' => $this->type->apiResult(),
            'kind' => $this->kind->apiResult(),
            'start_time' => substr($this->start_time , 0 , -3),
            'end_time' => substr($this->end_time, 0 , -3),
            'date_visit' => verta($this->date_visit)->format('%d %B %Y'),
            'date_visit_format' => verta($this->date_visit)->format('l j F Y'),
            'doctor'    => DoctorResource::make($this->doctor),
            'deadline_payment' => $this->deadline_at ? Carbon::parse($this->deadline_at)->diffForHumans(): null,
            'transaction' => $this->lastTransaction(),
            'payment_status' => $this->details[AppointmentUser::DETAIL_PAYMENT],
            'payment_link' => route('appointmentUser.payment', $this),
        ];
    }
}
