<!-- start sidebar -->
<aside class="sidebar__container">
    <ul class="sidebar__menu">
        <li class="sidebar__menu-item">
            <a href="{{ route('front.homePage') }}">صفحه اصلی</a>
        </li>
        @if (settingVfc($settingValues, Modules\Setting\Enum\SettingKeyEnum::SHOW_ABOUT_US_MENU_BUTTON))
            <li class="sidebar__menu-item">
                <a href="{{ route('front.aboutUs') }}">درباره ما</a>
            </li>
        @endif
        @if (settingVfc($settingValues, Modules\Setting\Enum\SettingKeyEnum::ENABLE_CONTACT_US_MENU))
            <li class="sidebar__menu-item">
                <a href="{{ route('front.contactUs') }}">ارتباط با ما</a>
            </li>
        @endif
        @if (settingVfc($settingValues, Modules\Setting\Enum\SettingKeyEnum::ENABLE_DOCTORS_MENU))
            <li class="sidebar__menu-item">
                <a href=""{{ route('front.searchPage', ['query' => 'پزشکان']) }}">لیست پزشکان</a>
            </li>
        @endif
        @guest
            <li class="sidebar__menu-item">
                <a href="{{ route('front.login.user') }}">ورود</a>
            </li>
            <li class="sidebar__menu-item">
                <a href="{{ route('front.login.doctor') }}">ورود پزشک</a>
            </li>
        @endguest
        @auth
            @if (auth()->user()->can('ADMIN_ACCESS'))
                <li class="sidebar__menu-item">
                    <a href="{{ route('admin.dashboard') }}">پنل مدیریت</a>
                </li>
            @else
                <li class="sidebar__menu-item">
                    <a href="{{ route('front.user.profile') }}">مشاهده پروفایل</a>
                </li>
            @endif
            <li class="sidebar__menu-item">
                <a href="{{ route('front.logout') }}">خروج</a>
            </li>
        @endauth
    </ul>
</aside>
<!-- end sidebar -->
