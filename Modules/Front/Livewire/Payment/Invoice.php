<?php

namespace Modules\Front\Livewire\Payment;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Http\Request;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Modules\Discount\app\Models\Discount;
use Modules\Transaction\app\Models\Transaction;
use Modules\Transaction\Enum\TransactionStatusEnum;
use Modules\Api\Http\Controllers\PaymentController;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;

#[Layout('front::layouts.app')]
class Invoice extends Component
{
    #[Validate('string|required|max:225')]
    public $discountCode;

    #[Locked]
    public $fetch = [
        'discountCodeApplied' => false,
    ];
    public $transaction = null;
    public $isPaymentDone =  false;
    public $message = '';
    public $user;


    public function messags()
    {
        return [
            'discountCode.string'   => 'فرمت وارد شده قابل قبول نیست!',
            'discountCode.required' => 'وارد کردن کد الزامی است!',
            'discountCode.max'      => 'طول کد تخفیف زیاد است!',
        ];
    }

    public function applyDiscount()
    {
        $this->validate();

        //check if code has been used once
        $discount = Discount::where('code', $this->discountCode)
            ->where('active', true)
            ->where(function ($query) {
                $query->where('start_at', '<', \now())
                    ->orWhereNull('start_at');
            })
            ->where(function ($query) {
                $query->where('end_at', '>', \now())
                    ->orWhereNull('end_at');
            })
            ->first();
        if ($discount->exists()) {
            $total_usage = $discount->detail[Discount::DETAIL_TOTAL_USAGE];
            $max_user_usage = $discount->detail[Discount::DETAIL_MAXIMUM_USAGE_EACH_USER];
            $minum_pice = $discount->detail[Discount::DETAIL_MINIMUM_PRICE];
            $maximum_price = $discount->detail[Discount::DETAIL_MAXIMUM_PRICE];
            $discount_type = $discount->detail[Discount::DETAIL_DISCOUNT_TYPE];
            $discount_amount = $discount->detail[Discount::DETAIL_DISCOUNT_AMOUNT];
            $user_old_transactions = Transaction::where('user_id', auth()->user()->id)->whereNotNull('discount_code')->get();
            $discount_code_usead_before = 1;
            foreach ($user_old_transactions as $old_t) {
                if (isset($old_t->discount_code) && $old_t->discount_code == $this->discountCode) {
                    $discount_code_usead_before++;
                }
            }
            if ($discount_code_usead_before > $max_user_usage) {
                return $this->addError('discountCode', 'شما قبلا از این کد استفاده کردید');
            } elseif ($total_usage < $discount->usage_counter) {
                return $this->addError('discountCode', 'سقف استفاده از این کد تخفیف به اتمام رسیده است');
            } elseif ($this->transaction->total_cost < $minum_pice) {
                return $this->addError('discountCode', 'مبلغ قابل پرداخت شما ، از حداقل مبلق قابل استفاده برای کد تخفیف کمتر است');
            } elseif ($this->transaction->total_cost > $maximum_price) {
                return $this->addError('discountCode', 'مبلغ قابل پرداخت شما ، از حداکثر مبلق قابل استفاده برای کد تخفیف بیشتر است');
            } else {
                if ($discount_type == 'percentage') {
                    $discouted_amount = $this->transaction->total_cost - (($this->transaction->total_cost * $discount_amount) / 100);
                } else {
                    $discouted_amount = $this->transaction->total_cost - $discount_amount;
                }
                $this->transaction->update([
                    'total_cost'    =>  $discouted_amount,
                    'discount_amount'      =>  $this->transaction->cost - $discouted_amount,
                    'discount_code'        =>  $this->discountCode,
                ]);
                $discount->update([
                    'usage_counter' => $discount->usage_counter + 1,
                ]);
            }
            if (!$this->fetch['discountCodeApplied']) {
                $this->fetch['discountCodeApplied'] = true;
            }
        }
    }

    public function createZarinPalReqeust()
    {
        $amount = $this->transaction->total_cost . '0';
        $transactionData = [
            'status' => 2,
            'cost' => $amount . '0',
            'detail' => '',
        ];
        $data = [
            'merchant_id' => env('ZARINPAL_MERCHANT_ID'),
            'amount' => intval($amount),
            'callback_url' => route('front.payment.invoice.callBack') . '?transaction_id=' . $this->transaction->id,
            'currency' => 'IRR',
            'description' => "پرداخت زرین پال",
            'metadata' => ['mobile' => $this->transaction->user->mobile],
        ];
        if (isset($this->transaction->detail)) {
            $this->transaction->update([
                'detail' => array_merge($this->transaction->detail, $data),
            ]);
        } else {
            $this->transaction->update([
                'detail' => $data
            ]);
        }
        $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/request.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data)),
        ]);

        try {
            $result = curl_exec($ch);
            $err = curl_error($ch);
            $result = json_decode($result, true, JSON_PRETTY_PRINT);
            curl_close($ch);
            if ($err) {
                return $this->addError('payment', 'مشکل در ارسال به بانک. لطفا صفحه را رفرش و مجدد تلاش کنید');
            } else {
                if (isset($result['data']['code']) && $result['data']['code'] === 100) {
                    $au = $result['data']['authority'] ?? '';
                    $this->transaction->update([
                        'detail' =>
                        array_merge([['au' => $au],  $this->transaction->detail]),
                    ]);
                    redirect("https://www.zarinpal.com/pg/StartPay/$au")->send();
                    return;
                } else {
                    return $this->addError('payment', 'مشکل در ارسال به بانک. لطفا صفحه را رفرش و مجدد تلاش کنید');
                }
            }
            return;
        } catch (\Exception $e) {
            $this->addError('payment', $e->getMessage());
        }
    }
    public function zarinCallback()
    {
        $user = $this->user;
        $au = request()->input('Authority');
        $status = request()->input('Status');
        $merchant_id =  env('ZARINPAL_MERCHANT_ID');
        $verifyUrl = 'https://api.zarinpal.com/pg/v4/payment/verify.json';
        $amount = $this->transaction->total_cost . '0';
        if ($status == 'OK') {
            $data = ['merchant_id' => $merchant_id, 'authority' => $au, 'amount' => intval($amount)];
            $jsonData = json_encode($data);
            $ch = curl_init($verifyUrl);
            curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v4');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData),
            ]);
            try {
                $result = curl_exec($ch);
                curl_close($ch);
                $result = json_decode($result, true);
                if ($result['data']['code'] == 100) {
                    $refId = $result['data']['ref_id'];
                    $this->transaction->update([
                        'status' => TransactionStatusEnum::SUCCESSFUL,
                        'detail' => json_encode($result),
                    ]);
                    //successful payment
                    $this->successfulPayment();
                } elseif ($result['data']['code'] == 101) {
                    //تراکنش قبلا وریفای شده است
                } else {
                    $this->transaction->update([
                        'status' => TransactionStatusEnum::PENDING,
                    ]);
                    session()->flash('error', 'خطا درانجام تراکنش');
                    return redirect()->route('front.payment.invoice');
                }
            } catch (\Exception $e) {
                session()->flash('error', 'خطا درانجام تراکنش');
                return redirect()->route('front.payment.invoice');
            }
            $this->successfulPayment();
        } else {
            session()->flash('error', 'پرداخت شما انجام نشد');
            return redirect()->route('front.payment.invoice');
        }
    }

    public function successfulPayment()
    {
        if ($this->transaction) {
            if ($this->transaction->status == TransactionStatusEnum::PENDING) {
                $this->transaction->transactionable->update([
                    'status' => AppointmentUserStatusEnum::STATUS_SUCCESSFUL,
                    'deadline_at' => null,
                ]);
                $this->isPaymentDone = true ;
            } elseif ($this->transaction->status == TransactionStatusEnum::REJECTED) {
                $this->addError('amount','پرداخت شما با مشکل مواجه شده و لطفا مجدد از طریق اپلیکیشن اقدام کنید');
                $this->isPaymentDone = false;
            } elseif ($this->transaction->status == TransactionStatusEnum::SUCCESSFUL) {
                $this->isPaymentDone = true ;
            }
        }
    }

    public function mount()
    {

        $this->user = auth()->user();
        $transaction =  Transaction::find(request()->route('transaction_id'));
        $this->transaction = $transaction;
        if (empty($transaction)) {
            $tid = \request()->input('transaction_id', null);
            $this->transaction = Transaction::findOrFail($tid);
            $this->zarinCallback();
        }
        if ($this->transaction->status == TransactionStatusEnum::SUCCESSFUL) {
            $this->message = 'پرداخت شما انجام شده و پکیج برای شما اختصاص یافته است';
        }
        $this->fetch['appointmentUser'] = $this->transaction->transactionable;
        $dead_line = Carbon::parse($this->fetch['appointmentUser']->deadline_at);
        if ($dead_line->isPast()) {
            $this->fetch['expired_time']  = false;
        } else {
            $this->fetch['expired_time'] = Carbon::now()->diffInHours($dead_line) . ' ساعت ';
            if ($this->fetch['expired_time'] == 0) {
                $this->fetch['expired_time'] = Carbon::now()->diffInMinutes($dead_line) . ' دقیقه ';
            }
        }
    }

    public function render()
    {
        return view('front::livewire.payment.invoice');
    }
}
