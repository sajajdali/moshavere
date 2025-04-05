<?php

namespace Modules\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{

    public function __construct($resource,private mixed $someoneName = null)
    {
        parent::__construct($resource);
    }
    public function toArray($request): array
    {
        $lastname = $this->last_name ?? '';
        if (isset($this->someoneName)) {
            $lastname = $this->last_name . ' ' . $this->someoneName;
        }
        return [
            'id' => (int) $this->id ?? 0,
            'mobile' => $this->mobile ?? '',
            'first_name' => $this->first_name ?? '',
            'last_name' => $lastname ?? '',
            'avatar' => $this->avatar ?? '',
            'age' => $this->age() ?? '',
            'national_code' => $this->national_code ?? '',
            'city' => $this->city ?? '',
            'birth_day' => $this->birthday ? json_decode($this->birthday) : null,
        ];
    }
}
