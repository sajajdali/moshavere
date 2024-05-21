<?php

namespace Modules\Admin\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Modules\Transaction\app\Models\Transaction;
use Modules\Api\Http\Controllers\PaymentController;
use Modules\Transaction\Enum\TransactionStatusEnum;

#[Title('پرداخت')]

#[Layout('admin::layouts.login')]

class Payment extends Component
{
    public $transaction = null;
    public $status = 'paying';
    public $message = '';
    public function mount()
    {
        $transaction = request()->route('transaction');
        if ($transaction instanceof Transaction){
            $this->transaction = $transaction;
            if ($transaction->status == TransactionStatusEnum::SUCCESSFUL) {
                $this->status = 'showMessage';
                $this->message = 'پرداخت شما انجام شده و پکیج برای شما اختصاص یافته است';
            }

        }
    }

    public function successfulPayment()
    {
        if ($this->transaction){
            if ($this->transaction->status == TransactionStatusEnum::PENDING) {
                $paymentController = new PaymentController();
                $response = $paymentController->assignToUser($this->transaction);
                if ($response->getStatusCode() == 200) {
                    $this->status = 'successful';
                } else {
                    $this->status = 'failed';
                }
            }
            elseif ($this->transaction->status == TransactionStatusEnum::REJECTED) {
                $this->status = 'showMessage';
                $this->message = 'پرداخت شما با مشکل مواجه شده و لطفا مجدد از طریق اپلیکیشن اقدام کنید';

            }
            elseif ($this->transaction->status == TransactionStatusEnum::SUCCESSFUL) {
                $this->status = 'showMessage';
                $this->message = 'پرداخت شما انجام شده و پکیج برای شما اختصاص یافته است';
            }
        }
    }

    public function paymentAgain()
    {
        $this->status = 'paying';
    }

    public function paymentFailed()
    {
        $this->status = 'failed';
    }
    public function render()
    {
        return view('admin::livewire.payment');
    }
}
