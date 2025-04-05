<!-- start sidebar -->
<aside class="sidebar__container">
    <ul class="sidebar__menu">
        <li class="sidebar__menu-item">
            <a href="{{ route('front.homePage') }}">خانه</a>
        </li>
        <li class="sidebar__menu-item">
            <a href="{{ route('front.aboutUs') }}">درباره ما</a>
        </li>
        <li class="sidebar__menu-item">
            <a href="{{ route('front.contactUs') }}">درخواست مشاوره</a>
        </li>
        <li class="sidebar__menu-item">
            <a href=""{{ route('front.searchPage', ['query' => 'پزشکان']) }}">لیست پزشکان</a>
        </li>
        <li class="sidebar__menu-item">
            <a href="{{ route('front.contactUs') }}">ثبت شکایات</a>
        </li>
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
