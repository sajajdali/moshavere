<?php

namespace Modules\Api\app\Resources\Api\Appointments;

use Illuminate\Http\Resources\Json\JsonResource;

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

    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'service' => $this->getServiceName(),
            'place' => $this->getPlaceName(),
            'tracking_code' => $this->tracking_code,
            'status' => $this->status->apiResult(),
            'type' => $this->type->apiResult(),
            'kind' => $this->kind->apiResult(),
            'start_time' => substr($this->start_time , 0 , -3),
            'end_time' => substr($this->end_time, 0 , -3),
            'date_visit' => verta($this->date_visit)->format('%d %B %Y'),
            'doctor'    => DoctorResource::make($this->doctor)
        ];
    }
}
