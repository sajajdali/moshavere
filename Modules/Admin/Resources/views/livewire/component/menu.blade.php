<nav class="admin-menu-navigation" aria-label="منوی مدیریت">
    <ul class="side-menu nav flex-column">
        @if(auth()->user()?->hasRole('عکاس'))
            <li class="nav-item">
                <a class="nav-link admin-menu-link side-menu__item hsa-link" href="{{ route('admin.dashboard') }}">
                    <i class="fa-solid fa-house main-icon icon-dashboard side-menu__icon"></i>
                    <span class="link-text side-menu__label">پیشخوان</span>
                </a>
            </li>
            <li class="nav-section-title"><h3>نوبت‌دهی</h3></li>
            <li class="nav-item">
                <a class="nav-link admin-menu-link side-menu__item hsa-link" href="{{ route('admin.appointment_user.list') }}">
                    <i class="fa-solid fa-calendar-check main-icon icon-calendar side-menu__icon"></i>
                    <span class="link-text side-menu__label">لیست نوبت‌های ثبت‌شده</span>
                </a>
            </li>
        @else
            @foreach($menuItems as $menu)
                @canany($menu['gate'], $menu['policy_class'])
                    @php($isAdminSection = ($menu['title'] ?? '') === 'مدیریت')

                    @if($isAdminSection)
                        @include('admin::livewire.component.menu-items', [
                            'items' => array_slice($menu['children'], 0, 2),
                            'depth' => 0,
                            'path' => 'admin-primary',
                        ])
                        @include('admin::livewire.component.menu-items', [
                            'items' => array_slice($menu['children'], 2),
                            'depth' => 0,
                            'path' => 'admin-clinic',
                        ])
                    @else
                        @if(isset($menu['has_divider']) && $menu['has_divider'])
                            <li class="nav-section-title">
                                <h3>{{ $this->sectionTitle($menu['title'] ?? '') }}</h3>
                            </li>
                        @endif

                        @include('admin::livewire.component.menu-items', [
                            'items' => $menu['children'],
                            'depth' => 0,
                            'path' => 'section-'.$loop->index,
                        ])
                    @endif
                @endcanany
            @endforeach
        @endif
    </ul>
</nav>
