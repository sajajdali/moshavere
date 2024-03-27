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
            'speciality' => $this->getSpeciality(),
            'avatar' => $this->avatar,
            'services' => ServiceResource::collection($this->service),
            'check_has_visited_or_not'   => true
        ];
    }
    public function getSpeciality()
    {
        $speciality = '';
        if($this->specialities->isNotEmpty()) {
            foreach ($this->specialities as $special) {
                if (strlen($speciality) < 1) {
                    $speciality .=  $special->title;
                } else {
                    $speciality .= ' - ' .  $special->title;
                }
            }
        }else{
            $speciality = 'بدون تخصص';
        }
        return $speciality;
    }
}
