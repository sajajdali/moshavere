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
        return [
            'id' => $this->id,
            'user' => UserResource::make($this->user),
            'status' =>$this->transaction()->first()->status->apiResult(),
            'payment_for' => $this->transaction()->first()->payment_for ? $this->transaction()->first()->payment_for->apiResult() : null,
            'paid_by' => $this->transaction()->first()->paid_by ? $this->transaction()->first()->paid_by->apiResult() : null,
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
