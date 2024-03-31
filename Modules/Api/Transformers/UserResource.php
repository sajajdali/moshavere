<?php

namespace Modules\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {

        return [
            'id' => (int) $this->id ?? 0,
            'mobile' => $this->mobile ?? '',
            'first_name' => $this->first_name ?? '',
            'last_name' => $this->last_name ?? '',
            'avatar' => $this->avatar ?? '',
            'age'=> $this->age() ?? '',
            'national_code' => $this->national_code ?? '',
        ];
    }
}
