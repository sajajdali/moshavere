<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Setting\Enum\SettingKeyEnum;

class DynamicPaymentConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->booted(function () {
            $activeGateway = setting(SettingKeyEnum::PAYMEN_ACTIVE_DRIVER);

            if ($activeGateway === 'parsian') {
                config([
                    'payment.default' => 'parsian',
                    'payment.drivers.parsian.merchantId' => setting(SettingKeyEnum::PAYMENT_PARSIAN_TOKEN),
                ]);
            } else {
                config([
                    'payment.default' => 'zarinpal',
                    'payment.drivers.zarinpal.merchantId' => setting(SettingKeyEnum::PAYMENT_ZARINPAL_MERCHENID),
                ]);
            }
        });
    }


    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
