<?php

namespace Modules\Api\app\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'int' => (int) $this['price'],
            'string' => number_format($this['price']),
            'currency' => 'ریال'
        ];
    }
}
