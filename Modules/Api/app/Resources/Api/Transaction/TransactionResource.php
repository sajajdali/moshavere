<?php

namespace Modules\Api\app\Resources\Api\Transaction;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Api\Transformers\UserResource;
use Modules\Transaction\Enum\TransactionStatusEnum;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => UserResource::make($this->user),
            'status' =>$this->status->apiResult(),
            'payment_for' => $this->payment_for ? $this->payment_for->apiResult() : $this->payment_for,
        ];
    }
}
