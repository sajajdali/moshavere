<?php

namespace Modules\Api\app\Resources\Api\Transaction;

use App\Enum\RouteEnum;
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
            'transaction_date' => verta($transaction->created_at)->format('l j F Y ساعت H:i دقیه'),
            'appointment' => [
                'tracking_url' => route('front.setAppointment.detail', ['tracking_code' => $this->tracking_code]),
                'start_time' => substr($this->start_time , 0 , -3),
                'end_time' => substr($this->end_time, 0 , -3),
                'date_visit' => verta($this->date_visit)->format('%d %B %Y'),
                'date_visit_format' => verta($this->date_visit)->format('l j F Y'),
                'doctor' => DoctorResource::make($this->doctor)
            ],
            'payment' => $this->payment()
        ];
    }

    private function payment()
    {
        $transaction = $this->transaction()->orderByDesc('id')->first();

        if (in_array($transaction->status , [TransactionStatusEnum::REJECTED , TransactionStatusEnum::PENDING]) ){
            return [
                'status' => true,
                'total_cost' => $transaction->total_cost,
                'payment_link' => route('api.appointment.payment.create', $this),
            ];
        }
        return [
            'status' => false,
            'total_cost' => 0,
            'payment_link' => null
        ];
    }
}
