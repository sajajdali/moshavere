<?php

namespace Modules\AppointmentUser\Livewire;

use Livewire\Component;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\Transaction\app\Models\Transaction;
use Modules\Transaction\Enum\TransactionPaidEnum;
use Modules\Transaction\Enum\TransactionPaymentForEnum;
use Modules\Transaction\Enum\TransactionStatusEnum;

class AppointmentUserPayment extends Component
{
    public $transaction = null;
    public $status = 'paying';
    public $message = '';
    public AppointmentUser $appointmentUser;

    public function mount(AppointmentUser $appointmentUser)
    {
        $this->appointmentUser = $appointmentUser;
    }

    public function successfulPayment()
    {

        if ($this->appointmentUser->status == AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT) {


            $this->status = 'showMessage';
            $this->message = 'پرداخت شما انجام شده و نوبت شما فعال شد';
            $this->transaction = $this->appointmentUser->transaction()->create([
                'transaction_code' => Transaction::generateTransactionCode(),
                'paid_by' => TransactionPaidEnum::ONLINE,
                'status' => TransactionStatusEnum::SUCCESSFUL,
                'cost' => $this->appointmentUser->details['payment'][AppointmentUser::DETAIL_PAYMENT_PRICE]['int'],
                'total_cost' => $this->appointmentUser->details['payment'][AppointmentUser::DETAIL_PAYMENT_PRICE]['int']
            ]);

            $this->appointmentUser->update([
                'status' => AppointmentUserStatusEnum::STATUS_SUCCESSFUL,
                'dead_line' => null
            ]);

        }
    }

    public function paymentAgain()
    {
        $this->status = 'paying';
    }

    public function paymentFailed()
    {
        $this->transaction = $this->appointmentUser->transaction()->create([
            'transaction_code' => Transaction::generateTransactionCode(),
            'paid_by' => TransactionPaidEnum::ONLINE,
            'status' => TransactionStatusEnum::PENDING,
            'cost' => $this->appointmentUser->details['payment'][AppointmentUser::DETAIL_PAYMENT_PRICE]['int'],
            'total_cost' => $this->appointmentUser->details['payment'][AppointmentUser::DETAIL_PAYMENT_PRICE]['int']
        ]);
        $this->status = 'failed';
    }

    public function render()
    {
        return view('admin::livewire.payment')->layout('admin::layouts.app_empty');
    }
}
