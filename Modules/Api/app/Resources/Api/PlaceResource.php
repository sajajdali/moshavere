<?php

namespace Modules\Api\app\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class PlaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'    => $this->id,
            'title' => $this->title,
            'location'  => isset($this->detail['location']) ? $this->detail['location'] : '',
            'priority'  => $this->priority,
            'created_at' => dateFormat($this->created_at)
        ];
    }
}
