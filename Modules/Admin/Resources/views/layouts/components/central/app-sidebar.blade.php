<!--APP-SIDEBAR-->
<div class="sticky">
    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
    <div class="app-sidebar">
        <div class="side-header">
            <a class="header-brand1 fw-bold fs-5" href="{{ route('central.dashboard') }}">پنل مرکزی</a>
        </div>
        <div class="main-sidemenu">
            <ul class="side-menu">
                <li><h3>مدیریت</h3></li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('central.dashboard') ? 'active' : '' }}" href="{{ route('central.dashboard') }}">
                        <i class="side-menu__icon fe fe-home"></i><span class="side-menu__label">سایت‌ها</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('central.new_site.*') ? 'active' : '' }}" href="{{ route('central.new_site.create') }}">
                        <i class="side-menu__icon fe fe-plus-circle"></i><span class="side-menu__label">افزودن سایت</span>
                    </a>
                </li>
                <li><h3>کاربران</h3></li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('central.customers.*') ? 'active' : '' }}" href="{{ route('central.customers.index') }}">
                        <i class="side-menu__icon fe fe-users"></i><span class="side-menu__label">مشتریان</span>
                    </a>
                </li>
                <li><h3>تنظیمات</h3></li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('central.renewal-settings') ? 'active' : '' }}" href="{{ route('central.renewal-settings') }}">
                        <i class="side-menu__icon fe fe-settings"></i><span class="side-menu__label">هزینه‌های تمدید</span>
                    </a>
                </li>
                @if (\Module::isEnabled('OnlineConsultation'))
                    @can('SUPER_ADMIN')
                        <li class="slide">
                            <a class="side-menu__item" href="{{ route('central.consultation.index') }}">
                                <i class="side-menu__icon fe fe-phone"></i><span class="side-menu__label">مشاوره آنلاین</span>
                            </a>
                        </li>
                    @endcan
                @endif
            </ul>
        </div>
    </div>
</div>
<!--/APP-SIDEBAR-->
