<?php

namespace Modules\Api\app\Resources\Api\Appointments;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Api\app\Resources\Api\ServiceResource;

class DoctorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'services' => ServiceResource::collection($this->service),
            'check_has_visited_or_not'   => true
        ];
    }
}
