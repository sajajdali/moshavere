<?php

namespace Modules\Api\app\Resources\Transaction;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Api\app\Resources\PriceResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'transaction_code' => $this->transaction_code,
            'price' => PriceResource::make(['price' => $this->cost]),
            'status' => [
                'title' => $this->status->getName(),
                'body' => $this->status->value
            ],
            'created_at' => dateFormat($this->created_at)
        ];
    }
}
