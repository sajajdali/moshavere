<?php

namespace App\Providers;

use File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Modules\Setting\Enum\SettingKeyEnum;
use Stancl\Tenancy\Events\TenancyBootstrapped;

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
        \Event::listen(TenancyBootstrapped::class, function () {
            $tenantId = tenant('id');

            if ($tenantId) {
                $basePath = storage_path('/');
                $paths = [
                    $basePath,
                    "$basePath/framework",
                    "$basePath/framework/cache",
                    "$basePath/framework/sessions",
                    "$basePath/framework/views",
                    "$basePath/logs",
                ];

                foreach ($paths as $path) {
                    if (!File::exists($path)) {
                        File::makeDirectory($path, 0777, true);
                    }
                }

            }
        });

        // Fetch the Zarinpal merchant ID from the settings
        $merchantId = setting(SettingKeyEnum::PAYMENT_ZARINPAL_MERCHENID);
        // Set the Zarinpal merchant ID dynamically
        config([
            'payment.drivers.zarinpal.merchantId' => $merchantId,
        ]);

        // ذخیره tenant_id هنگام dispatch شدن job
        \Queue::createPayloadUsing(function ($connection, $queue, $payload) {
            return [
                'tenant_id' => tenant()?->getTenantKey(),
            ];
        });

        // فعال‌سازی tenant هنگام اجرای job
        \Event::listen(\Illuminate\Queue\Events\JobProcessing::class, function ($event) {
            $payload = $event->job->payload();

            if (isset($payload['tenant_id'])) {
                tenancy()->initialize($payload['tenant_id']);
            }
        });
    }
}
