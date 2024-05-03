<?php

namespace Modules\Api\app\Resources\Api\Transaction;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Api\app\Resources\Api\Appointments\DoctorResource;
use Modules\Api\Transformers\UserResource;
use Modules\Transaction\Enum\TransactionStatusEnum;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        $transaction = $this->transaction()->orderByDesc('id')->first();
        return [
            'id' => $transaction->id,
            'user' => UserResource::make($this->user),
            'status' =>$transaction->status->apiResult(),
            'payment_for' =>$transaction->payment_for ? $transaction->payment_for->apiResult() : null,
            'paid_by' => $transaction->paid_by ? $transaction->paid_by->apiResult() : null,
            'appointment' => [
                'start_time' => substr($this->start_time , 0 , -3),
                'end_time' => substr($this->end_time, 0 , -3),
                'date_visit' => verta($this->date_visit)->format('%d %B %Y'),
                'date_visit_format' => verta($this->date_visit)->format('l j F Y'),
                'doctor' => DoctorResource::make($this->doctor)
            ]
        ];
    }
}
