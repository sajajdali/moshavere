<?php

namespace Modules\Api\app\Resources\Api\Transaction;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Transaction\Enum\TransactionStatusEnum;

class TransactionPaginateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'transactions' => TransactionResource::collection($this),
            'status' => TransactionStatusEnum::all(),
            'paginate' => [
                'current_page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'last_page' => $this->lastPage()
            ],
        ];
    }
}
