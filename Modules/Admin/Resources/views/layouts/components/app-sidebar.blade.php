<!--APP-SIDEBAR-->
<div class="app-sidebar__overlay sidebar-overlay" id="adminSidebarOverlay"></div>

<aside class="app-sidebar sidebar" id="adminSidebar" aria-label="منوی اصلی">
    <div class="sidebar-brand">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand-link">
            <span class="sidebar-brand-mark">
                <img src="{{ setting(Modules\Setting\Enum\SettingKeyEnum::SITE_LOGO_URL) }}" alt="{{ setting(Modules\Setting\Enum\SettingKeyEnum::SITE_TITLE) }}">
            </span>
            <span class="sidebar-brand-text">{{ setting(Modules\Setting\Enum\SettingKeyEnum::SITE_TITLE) }}</span>
        </a>
    </div>
    <div class="main-sidemenu">
        <livewire:admin::component.menu />
    </div>
</aside>

<button class="sidebar-toggle-desktop" id="adminDesktopToggle" type="button"
    aria-label="جمع کردن منو" aria-controls="adminSidebar" aria-expanded="true">
    <i class="fa-solid fa-bars" aria-hidden="true"></i>
</button>
<!--/APP-SIDEBAR-->
