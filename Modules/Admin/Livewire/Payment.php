<?php

namespace Modules\Admin\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\Api\Http\Controllers\PaymentController;
use Modules\Transaction\Entities\Transaction;
use Modules\Transaction\Enum\TransactionStatusEnum;

#[title('پرداخت')]

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
