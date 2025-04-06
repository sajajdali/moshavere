<?php

namespace Modules\Api\app\Resources\Api\Appointment\online;

use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentOnlineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
