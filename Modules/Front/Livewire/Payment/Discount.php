<?php

namespace Modules\Front\Livewire\Payment;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Modules\Transaction\app\Models\Transaction;
use Modules\Transaction\Enum\TransactionStatusEnum;
use Modules\Api\Http\Controllers\PaymentController;

#[Layout('front::layouts.app')]
class Discount extends Component
{
    #[Validate('string|required|max:225')]
    public $discountCode;

    #[Locked]
    public $fetch = [
        'discountCodeApplied' => false,
    ];
    public $transaction = null;
    public $status = 'paying';
    public $message = '';
    public function messags()
    {
        return [
            'discountCode.string' => 'فرمت وارد شده قابل قبول نیست!',
            'discountCode.required' => 'وارد کردن کد الزامی است!',
            'discountCode.max' => 'طول کد تخفیف زیاد است!',
        ];
    }
    public function applyDiscount()
    {
        $this->validate();
        //check if code has been used once
        if (!$this->fetch['discountCodeApplied']) {
            $this->fetch['discountCodeApplied'] = true;
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

    public function mount()
    {
        $transaction = request()->route('transaction');
        if ($transaction instanceof Transaction) {
            $this->transaction = $transaction;
            if ($transaction->status == TransactionStatusEnum::SUCCESSFUL) {
                $this->status = 'showMessage';
                $this->message = 'پرداخت شما انجام شده و پکیج برای شما اختصاص یافته است';
            }
        }
    }
    public function render()
    {
        return view('front::livewire.payment.discount');
    }
}
