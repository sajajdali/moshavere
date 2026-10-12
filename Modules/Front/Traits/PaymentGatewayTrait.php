<?php

namespace Modules\Front\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Setting\Enum\SettingKeyEnum;
use Shetabit\Multipay\Contracts\ReceiptInterface;
use Shetabit\Multipay\Invoice;
use Shetabit\Multipay\RedirectionForm;
use Shetabit\Payment\Facade\Payment;

/**
 * انتخاب درگاه فعال، هدایت به بانک و تایید پرداخت.
 * بین صفحه جزئیات نوبت (قالب قدیم) و لینک پرداخت قالب جدید (اپ React) مشترک است.
 */
trait PaymentGatewayTrait
{
    /**
     * درگاه فعال در تنظیمات. «zrinpal» کلید ذخیره شده گزینه زرین پال است.
     */
    protected function activePaymentGateway(): string
    {
        return $this->normalizePaymentGateway(setting(SettingKeyEnum::PAYMEN_ACTIVE_DRIVER));
    }

    /**
     * درگاهی که تراکنش با آن ساخته شده؛ تایید پرداخت باید با همان درگاه انجام شود
     * حتی اگر درگاه فعال در این فاصله تغییر کرده باشد.
     */
    protected function transactionPaymentGateway(?array $transactionDetail): string
    {
        $driver = $transactionDetail['driver'] ?? null;

        return $driver ? $this->normalizePaymentGateway($driver) : $this->activePaymentGateway();
    }

    private function normalizePaymentGateway(?string $gateway): string
    {
        return in_array($gateway, [null, '', 'zrinpal'], true) ? 'zarinpal' : $gateway;
    }

    /**
     * مقادیر درگاه را از تنظیمات سایت در config پرداخت قرار میدهد.
     * درگاه پیش فرض هم باید همان درگاه باشد چون تنظیمات درایور از درگاه پیش فرض خوانده میشود.
     */
    protected function configurePaymentGateway(string $gateway): void
    {
        $driverConfig = match ($gateway) {
            'parsian' => [
                'payment.drivers.parsian.merchantId' => setting(SettingKeyEnum::PAYMENT_PARSIAN_TOKEN),
            ],
            'saman' => [
                'payment.drivers.saman.merchantId' => setting(SettingKeyEnum::PAYMENT_SAMAN_TERMINAL_NUMBER),
            ],
            'sep' => [
                'payment.drivers.sep.terminalId' => setting(SettingKeyEnum::PAYMENT_SAMAN_TERMINAL_NUMBER),
            ],
            'zibal' => [
                'payment.drivers.zibal.merchantId' => setting(SettingKeyEnum::PAYMENT_ZIBAL_MERCHENID),
            ],
            'sadad' => [
                'payment.drivers.sadad.merchantId' => setting(SettingKeyEnum::PAYMENT_SADAD_MERCHANT_ID),
                'payment.drivers.sadad.terminalId' => setting(SettingKeyEnum::PAYMENT_SADAD_TERMINAL_ID),
                'payment.drivers.sadad.key' => setting(SettingKeyEnum::PAYMENT_SADAD_KEY),
            ],
            default => [
                'payment.drivers.zarinpal.merchantId' => setting(SettingKeyEnum::PAYMENT_ZARINPAL_MERCHENID),
            ],
        };

        config(['payment.default' => $gateway] + $driverConfig);
    }

    /**
     * تراکنش را در درگاه ثبت میکند.
     * در صورت خطای سامان null برمیگردد؛ خطای سایر درگاه ها به صورت exception منتقل میشود.
     *
     * @return array{transactionId: string, url: string}|null
     */
    protected function purchaseFromGateway(string $gateway, int $amount, string $description, string $callbackUrl, ?string $mobile = null): ?array
    {
        $this->configurePaymentGateway($gateway);

        if ($gateway === 'saman') {
            $response = Http::post('https://sep.shaparak.ir/onlinepg/onlinepg', [
                'action'      => 'token',
                'TerminalId'  => setting(SettingKeyEnum::PAYMENT_SAMAN_TERMINAL_NUMBER),
                'Amount'      => $amount,
                'ResNum'      => Str::uuid()->toString(),
                'RedirectUrl' => $callbackUrl,
                'CellNumber'  => $mobile,
            ]);

            $data = $response->json();
            if (! isset($data['status']) || $data['status'] != 1) {
                Log::error('saman gateway error', [
                    'e-code' => $data['errorCode'] ?? 'unknown',
                    'messageg' => $data['errorDesc'] ?? 'Request failed',
                ]);

                return null;
            }

            return [
                'transactionId' => $data['token'],
                'url' => route('payment.saman.form', ['token' => $data['token']]),
            ];
        }

        $transactionId = null;
        $invoice = (new Invoice)->amount($this->gatewayAmount($gateway, $amount))->detail('description', $description);
        $form = Payment::via($gateway)
            ->callbackUrl($callbackUrl)
            ->purchase($invoice, function ($driver, $id) use (&$transactionId) {
                $transactionId = $id;
            })
            ->pay();

        // سپ توکن را با فرم POST میخواهد؛ همان فرم انتقال سامان استفاده میشود
        $url = $gateway === 'sep'
            ? route('payment.saman.form', ['token' => $transactionId])
            : $this->gatewayRedirectUrl($form);

        return ['transactionId' => $transactionId, 'url' => $url];
    }

    /**
     * آدرس هدایت به بانک. در فرم های GET پارامترهایی که در آدرس نیستند
     * (مثل Token سداد) به آدرس اضافه میشوند.
     */
    protected function gatewayRedirectUrl(RedirectionForm $form): string
    {
        $action = $form->getAction();
        if (strtoupper($form->getMethod()) !== 'GET' || $form->getInputs() === []) {
            return $action;
        }

        parse_str((string) parse_url($action, PHP_URL_QUERY), $existing);
        $missing = array_diff_key($form->getInputs(), $existing);

        return $missing === []
            ? $action
            : $action . (str_contains($action, '?') ? '&' : '?') . http_build_query($missing);
    }

    protected function verifyGatewayPayment(string $gateway, int $amount, string $transactionId): ReceiptInterface
    {
        $this->configurePaymentGateway($gateway);

        return Payment::via($gateway)
            ->amount($this->gatewayAmount($gateway, $amount))
            ->transactionId($transactionId)
            ->verify();
    }

    /**
     * مبالغ سایت به ریال است؛ درایورهایی که واحدشان تومان تنظیم شده خودشان مبلغ را ×۱۰ میکنند.
     */
    protected function gatewayAmount(string $gateway, int $amount): int
    {
        return config("payment.drivers.$gateway.currency") === 'T' ? intdiv($amount, 10) : $amount;
    }

    /**
     * اطلاعات پرداخت موفق (شماره مرجع و کارت) برای ذخیره در جزئیات تراکنش.
     *
     * @param \Shetabit\Multipay\Receipt $receipt
     */
    protected function gatewayReceiptDetail(string $gateway, $receipt): array
    {
        $details = $receipt->getDetails();

        return match ($gateway) {
            'saman' => [
                'card_hash' => request()->post('SecurePan', null),
                'ref_id'    => request()->post('ResNum', null),
            ],
            'sadad' => [
                'ref_id'   => $details['referenceNo'] ?? null,
                'trace_no' => $details['traceNo'] ?? null,
            ],
            default => isset($details['card_hash'], $details['ref_id'])
                ? ['card_hash' => $details['card_hash'], 'ref_id' => $details['ref_id']]
                : [],
        };
    }
}
