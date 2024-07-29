<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Modules\Setting\Enum\SettingKeyEnum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fetch the Zarinpal merchant ID from the settings
        $merchantId = setting(SettingKeyEnum::PAYMENT_ZARINPAL_MERCHENID);
        // Set the Zarinpal merchant ID dynamically
        config([
            'payment.drivers.zarinpal.merchantId' => $merchantId,
        ]);
    }
}
