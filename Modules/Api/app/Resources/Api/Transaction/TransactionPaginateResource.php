<?php

namespace Modules\Api\app\Resources\Api\Transaction;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionPaginateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'transactions' => TransactionResource::collection($this),
            'paginate' => [
                'current_page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'last_page' => $this->lastPage()
            ],
        ];
    }
}
