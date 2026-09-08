<?php

namespace Modules\Admin\Livewire\Component;

use Livewire\Component;

class Menu extends Component
{
    public array $menuItems = [];

    public function mount()
    {
        $modules = \Module::allEnabled();
        foreach ($modules as $module) {
            if (! \App\Support\TenantModuleAccess::enabled($module->getName())) {
                continue;
            }
            if ($module->getName() === 'OnlineConsultation' && !\Modules\OnlineConsultation\Support\ConsultationAccess::enabled()) {
                continue;
            }
            $menu = config($module->getLowerName().'.menu') ?? [];
            if (is_array($menu) && count($menu) > 0) {
                $this->menuItems[] = $menu;
            }
        }

        $sectionOrder = [
            'مدیریت',
            'نوبتدهی',
            'تعاریفپایه',
            'امورمالی',
            'باشگاهمشتریان',
            'تنظیمات',
            'نمایش',
            'بخشکاربری',
            'بخشکاربران',
        ];

        $itemOrder = [
            'مدیریت' => [
                'پیشخوان',
                'گزارشات',
                'نوبتهای در انتظار تایید',
                'فرم ساز',
                'پرامت',
                'لیست انتظار',
                'اتاق پزشک',
                'اتاق پروسیجر',
                'اتاق حسابداری',
                'نمایش سالن انتظار',
                'سالن انتظار با تصویر',
            ],
            'نوبتدهی' => [
                'لیست نوبتهای ثبتشده',
                'نوبتهای امروز',
                'نوبتهای معاینه آنلاین',
                'نوبتهای رزرو جراحی',
                'تنظیمات نوبتدهی',
                'ثبت نوبت جدید',
                'وارد کردن نوبت با اکسل',
            ],
            'بخشکاربری' => [
                'کاربران',
                'بازخورد بیماران',
                'نقشها',
            ],
            'تعاریفپایه' => [
                'پیامهای یادآوری',
                'تخصصها',
                'مدیریت مطب',
                'بخشها',
            ],
            'امورمالی' => [
                'تراکنشها',
            ],
            'باشگاهمشتریان' => [
                'افزودن عضو',
                'امتیازها',
                'سطحبندی کاربران',
                'مزایا و پاداشها',
                'تعامل با کاربران',
                'گزارشها',
            ],
            'نمایش' => [
                'سوالات متداول',
                'کامنتها',
                'فرم تماس با ما',
            ],
            'تنظیمات' => [
                'تنظیمات',
            ],
        ];

        $this->menuItems = $this->sortByReferenceOrder($this->menuItems, $sectionOrder);

        foreach ($this->menuItems as &$section) {
            $sectionKey = $this->normalizeTitle($section['title'] ?? '');
            if (!empty($section['children']) && isset($itemOrder[$sectionKey])) {
                $section['children'] = $this->sortByReferenceOrder(
                    $section['children'],
                    $itemOrder[$sectionKey]
                );
            }
        }
        unset($section);
    }

    public function sectionTitle(string $title): string
    {
        return match ($this->normalizeTitle($title)) {
            'نوبتدهی' => 'نوبت‌دهی',
            'بخشکاربری', 'بخشکاربران' => 'بخش کاربران',
            default => $title,
        };
    }

    public function iconClass(array $item, int $depth = 0): string
    {
        $title = $this->normalizeTitle($item['title'] ?? '');
        $route = (string) ($item['route'] ?? '');

        if ($depth > 0) {
            if ($title === 'افزودن' && str_contains($route, 'user.create')) {
                return 'fa-solid fa-user-plus admin-submenu-icon';
            }

            $childIcons = [
                'لیست' => 'fa-list',
                'افزودن' => 'fa-plus',
                'اضافهکردن' => 'fa-plus',
                'لیستدادهها' => 'fa-database',
                'مشاهدهلیستها' => 'fa-eye',
                'افزودنلیست' => 'fa-plus',
                'صفهایفعال' => 'fa-users-line',
                'فرمهایبازخورد' => 'fa-file-lines',
                'نتایجبازخورد' => 'fa-chart-pie',
                'خروجیکاربرانباشرط' => 'fa-file-export',
                'پیگیریوضعیتمشاوره' => 'fa-stethoscope',
                'قوانینامتیاز' => 'fa-gavel',
                'امتیازهایزمانبندیشده' => 'fa-clock',
                'ثبتامتیازدستی' => 'fa-hand-pointer',
                'امتیازهایدرانتظارتایید' => 'fa-hourglass-half',
                'سطحها' => 'fa-layer-group',
                'خدماتسطحبندی' => 'fa-concierge-bell',
                'پاداشها' => 'fa-gift',
                'پاداشهایخریداریشده' => 'fa-cart-shopping',
                'ماموریتهایماهانه' => 'fa-calendar-days',
                'تاییدماموریتها' => 'fa-circle-check',
                'دعوتبهعکاسی' => 'fa-camera',
                'گزارش' => 'fa-file-export',
            ];

            return 'fa-solid '.($childIcons[$title] ?? 'fa-circle').' admin-submenu-icon';
        }

        $mainIcons = [
            'پیشخوان' => ['fa-house', 'icon-dashboard'],
            'گزارشات' => ['fa-chart-column', 'icon-chart'],
            'نوبتهای'.'درانتظارتایید' => ['fa-circle-check', 'icon-check'],
            'فرمساز' => ['fa-file-pen', 'icon-form'],
            'پرامت' => ['fa-comment-dots', 'icon-prompt'],
            'پرامپت' => ['fa-comment-dots', 'icon-prompt'],
            'لیستانتظار' => ['fa-clipboard-list', 'icon-list'],
            'اتاقپزشک' => ['fa-user-doctor', 'icon-doctor'],
            'اتاقپروسیجر' => ['fa-kit-medical', 'icon-procedure'],
            'اتاقحسابداری' => ['fa-file-invoice-dollar', 'icon-accounting'],
            'نمایشسالنانتظار' => ['fa-tv', 'icon-display'],
            'سالنانتظارباتصویر' => ['fa-image', 'icon-image'],
            'لیستنوبتهایثبتشده' => ['fa-calendar-check', 'icon-calendar'],
            'بازخوردبیماران' => ['fa-comments', 'icon-feedback'],
            'نوبتهایامروز' => ['fa-calendar-day', 'icon-today'],
            'نوبتهای'.'معاینهآنلاین' => ['fa-laptop-medical', 'icon-online'],
            'نوبتهای'.'رزروجراحی' => ['fa-clock', 'icon-surgery'],
            'تنظیماتنوبتدهی' => ['fa-sliders', 'icon-settings'],
            'ثبتنوبتجدید' => ['fa-circle-plus', 'icon-plus'],
            'واردکردننوبتبااکسل' => ['fa-file-excel', 'icon-excel'],
            'کاربران' => ['fa-users', 'icon-users'],
            'نقشها' => ['fa-user-shield', 'icon-roles'],
            'پیامهاییادآوری' => ['fa-bell', 'icon-bell'],
            'تخصصها' => ['fa-stethoscope', 'icon-specialty'],
            'مدیریتمطب' => ['fa-hospital', 'icon-clinic'],
            'بخشها' => ['fa-sitemap', 'icon-sections'],
            'تراکنشها' => ['fa-money-bill-transfer', 'icon-money'],
            'افزودنعضو' => ['fa-user-plus', 'icon-member'],
            'امتیازها' => ['fa-star', 'icon-star'],
            'سطحبندیکاربران' => ['fa-trophy', 'icon-trophy'],
            'مزایاوپاداشها' => ['fa-gift', 'icon-gift'],
            'تعامل'.'باکاربران' => ['fa-comments', 'icon-chat'],
            'گزارشها' => ['fa-chart-column', 'icon-chart'],
            'سوالاتمتداول' => ['fa-circle-question', 'icon-specialty'],
            'کامنتها' => ['fa-comments', 'icon-chat'],
            'فرمتماسباما' => ['fa-envelope', 'icon-list'],
            'تنظیمات' => ['fa-gear', 'icon-gear'],
            'اپلیکیشن' => ['fa-mobile-screen-button', 'icon-online'],
            'اتصالبهنرمافزار' => ['fa-plug', 'icon-settings'],
            'بخشبندینوبتنواحیبدن' => ['fa-person', 'icon-sections'],
            'پرداختاعتباریباجیبیت' => ['fa-wallet', 'icon-money'],
            'پشتیبانان' => ['fa-headset', 'icon-users'],
            'پیامهاینوبتآنلاین' => ['fa-message', 'icon-chat'],
            'پیامک' => ['fa-comment', 'icon-chat'],
            'پیغامهایضبطشده' => ['fa-voicemail', 'icon-prompt'],
            'تماسهایورودی' => ['fa-phone-volume', 'icon-online'],
            'تنظماتوبسایت' => ['fa-globe', 'icon-settings'],
            'تنظیماتAPI' => ['fa-plug', 'icon-settings'],
            'تنظیماتVoip' => ['fa-phone', 'icon-settings'],
            'تنظیماتاپ' => ['fa-mobile-screen', 'icon-settings'],
            'تنظیماتپرداخت' => ['fa-credit-card', 'icon-money'],
            'تنظیماتعمومی' => ['fa-sliders', 'icon-settings'],
            'داشبوردمشاوره' => ['fa-headset', 'icon-online'],
            'پزشکانوکارشناسان' => ['fa-user-doctor', 'icon-doctor'],
            'تنظیماتمشاوره' => ['fa-sliders', 'icon-settings'],
            'داشبوردمشاورانتلفنی' => ['fa-headset', 'icon-online'],
            'گزارشتماسها' => ['fa-phone-volume', 'icon-chart'],
            'گزارشپیامکهایمشاوره' => ['fa-message', 'icon-chat'],
            'لاگتماسهایVoIP' => ['fa-list', 'icon-list'],
            'تنظیماتهدر' => ['fa-heading', 'icon-settings'],
            'ثبتنامبیماران' => ['fa-user-plus', 'icon-member'],
            'درخواستهایمشاوره' => ['fa-headset', 'icon-feedback'],
            'صفحهیتماسباما' => ['fa-envelope', 'icon-list'],
            'صفحهیدربارهیما' => ['fa-circle-info', 'icon-specialty'],
            'عدمحضور' => ['fa-user-xmark', 'icon-surgery'],
            'کدتخفیف' => ['fa-percent', 'icon-gift'],
            'متنهایپیامکپارسوفراز' => ['fa-file-lines', 'icon-bell'],
            'مدیریتنوبتدهی' => ['fa-calendar-days', 'icon-calendar'],
            'نظرسنجی' => ['fa-square-poll-vertical', 'icon-chart'],
            'نوبتآنلاین' => ['fa-laptop-medical', 'icon-online'],
        ];

        [$icon, $color] = $mainIcons[$title] ?? ['fa-circle', 'icon-gear'];

        return "fa-solid {$icon} main-icon {$color}";
    }

    public function badgeCount(string $className): int
    {
        if (method_exists($className, 'badgeCount')) {
            return $className::badgeCount();
        }

        return 0;
    }

    private function normalizeTitle(string $title): string
    {
        $title = str_replace(['ي', 'ك'], ['ی', 'ک'], trim($title));

        return preg_replace('/[\s‌ـ\-–—()]+/u', '', $title) ?? $title;
    }

    private function sortByReferenceOrder(array $items, array $orderedTitles): array
    {
        $positions = [];
        foreach ($orderedTitles as $position => $title) {
            $positions[$this->normalizeTitle($title)] = $position;
        }

        $fallbackPosition = count($positions);
        $decoratedItems = [];

        foreach (array_values($items) as $originalPosition => $item) {
            $title = $this->normalizeTitle($item['title'] ?? '');
            $decoratedItems[] = [
                'position' => $positions[$title] ?? ($fallbackPosition + $originalPosition),
                'original_position' => $originalPosition,
                'item' => $item,
            ];
        }

        usort($decoratedItems, static function (array $a, array $b): int {
            return [$a['position'], $a['original_position']]
                <=> [$b['position'], $b['original_position']];
        });

        return array_column($decoratedItems, 'item');
    }

    public function render()
    {
        return view('admin::livewire.component.menu');
    }
}
