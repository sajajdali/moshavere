<?php

namespace Modules\Api\app\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class SomeoneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'mobile' => $this->mobile,
            'gender' => $this->gender,
//            'age' => $this->age,
            'national_code' => $this->nationalCode,
            'acquainted' => $this->acquainted,
            'address' => $this->address,
            'city' => $this->city,
        ];
    }
}
