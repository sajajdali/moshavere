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

                // تنظیم دیسک برای مستاجر
//                config([
//                    'filesystems.disks.tenant' => [
//                        'driver' => 'local',
//                        'root' => storage_path('app/tenants/' . $tenantId . '/uploads'), // مسیر اختصاصی برای هر مستاجر
//                        'url' => url('    /').'/storage/tenants/'.$tenantId.'/uploads', // URL برای دسترسی به فایل‌ها
//                        'visibility' => 'public',
//                    ],
//
//                ]);
            }
        });

        // Fetch the Zarinpal merchant ID from the settings
        $merchantId = setting(SettingKeyEnum::PAYMENT_ZARINPAL_MERCHENID);
        // Set the Zarinpal merchant ID dynamically
        config([
            'payment.drivers.zarinpal.merchantId' => $merchantId,
        ]);
    }
}
