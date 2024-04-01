<?php

namespace Modules\Api\app\Resources\Api\Appointments;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AppointmentUserPaginateResource extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'appointments' => AppointmentUserResource::collection($this),
            'paginate' => [
                'current_page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'last_page' => $this->lastPage()
            ],
        ];
    }
}
