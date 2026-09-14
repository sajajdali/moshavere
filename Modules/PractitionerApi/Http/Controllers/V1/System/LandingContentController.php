<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\System;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\PractitionerApi\Services\PractitionerSettingsService;

#[Group('عمومی', 'اطلاعات عمومی اپلیکیشن که به توکن نیاز ندارد', weight: 0)]
class LandingContentController
{
    /**
     * محتوای صفحه معرفی اپلیکیشن
     *
     * این endpoint عمومی ولی Tenant-based است. محتوا از تنظیمات همان Tenant خوانده
     * می‌شود تا بدون انتشار نسخه جدید اپ قابل تغییر باشد. ترتیب features با order است.
     */
    #[ApiResponse(200, 'محتوای لندینگ Tenant.', type: 'array{data: array{app_name: string, app_subtitle: string, headline: string, description: string, features: list<array{order: int, title: string, body: string}>, cta_label: string, footnote: string, terms_url: string|null, min_supported_version: string}}', examples: [[
        'data' => [
            'app_name' => 'سیمین روان', 'app_subtitle' => 'پنل پزشکان و مشاوران',
            'headline' => 'نوبت‌ها، تماس‌ها و درآمد شما در یک جا',
            'description' => 'مدیریت نوبت‌های آنلاین، تماس و گزارش مشاوره.',
            'features' => [['order' => 1, 'title' => 'نوبت‌های امروز', 'body' => 'زمان نوبت‌ها را مشاهده کنید.']],
            'cta_label' => 'ورود به پنل پزشک', 'footnote' => 'ورود فقط برای پزشکان فعال مجموعه',
            'terms_url' => null, 'min_supported_version' => '1.0.0',
        ],
    ]])]
    public function __invoke(PractitionerSettingsService $settings): JsonResponse
    {
        return response()->json(['data' => $settings->landing()]);
    }
}
