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
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'    => $this->id,
            'service'   => $this->service->apiResult(),
            'place' => $this->place->apiResult(),
            'doctor'    =>  $this->apiResultUser($this->doctor),
            'tracking_code' => $this->tracking_code,
            'status' => $this->status->apiResult(),
            'type' => $this->type->apiResult(),
            'kind' => $this->kind->apiResult(),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'date_visit' => verta($this->date_visit)->format('%d %B %Y'),
        ];
    }
}
