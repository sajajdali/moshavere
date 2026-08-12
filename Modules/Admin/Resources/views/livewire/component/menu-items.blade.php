@foreach($items as $index => $innerItem)
    @if(isset($innerItem['auth_user_id']) && auth()->id() !== (int) $innerItem['auth_user_id'])
        @continue
    @endif

    @if(isset($innerItem['only_user_id']) && auth()->id() !== (int) $innerItem['only_user_id'])
        @continue
    @endif

    @if(isset($innerItem['setting_key']) && !filter_var(setting($innerItem['setting_key']), FILTER_VALIDATE_BOOLEAN))
        @continue
    @endif

    @canany($innerItem['gate'], $innerItem['policy_class'])
        @php
            $href = '#';

            if (!$innerItem['has_child'] && !empty($innerItem['route'])) {
                if (\Route::has($innerItem['route'])) {
                    $routeParams = $innerItem['param'] ?? [];
                    $queryParams = $innerItem['query_param'] ?? [];
                    $href = route($innerItem['route'], $routeParams);

                    if (!empty($queryParams)) {
                        $href .= '?' . http_build_query($queryParams);
                    }
                } else {
                    $href = $innerItem['route'];
                }
            }

            $itemPath = $path.'-'.$index;
            $submenuId = 'admin-submenu-'.substr(md5($itemPath.'-'.($innerItem['title'] ?? '')), 0, 12);
        @endphp

        <li class="nav-item {{ $innerItem['has_child'] ? 'admin-menu-parent' : '' }}">
            <a
                class="nav-link admin-menu-link {{ $depth === 0 ? 'side-menu__item' : 'admin-submenu-link' }} {{ (!$innerItem['has_child'] && $depth === 0) ? 'hsa-link' : '' }}"
                @if($innerItem['has_child'])
                    data-bs-toggle="collapse"
                    href="#{{ $submenuId }}"
                    role="button"
                    aria-expanded="false"
                    aria-controls="{{ $submenuId }}"
                @else
                    href="{{ $href }}"
                @endif
            >
                <i class="{{ $this->iconClass($innerItem, $depth) }} {{ $depth === 0 ? 'side-menu__icon' : '' }}"></i>
                <span class="{{ $depth === 0 ? 'link-text side-menu__label' : 'submenu-link-text' }}">
                    {{ $innerItem['title'] }}
                </span>

                @if(isset($innerItem['has_badge']) && $innerItem['has_badge'] && $this->badgeCount($innerItem['badge']['class']) > 0)
                    <span class="badge-notification">
                        {{ $this->badgeCount($innerItem['badge']['class']) }}
                    </span>
                @endif

                @if($innerItem['has_child'])
                    <i class="fa-solid fa-chevron-left arrow" aria-hidden="true"></i>
                @endif
            </a>

            @if($innerItem['has_child'])
                <div class="collapse submenu" id="{{ $submenuId }}">
                    <ul class="nav flex-column">
                        @include('admin::livewire.component.menu-items', [
                            'items' => $innerItem['children'],
                            'depth' => $depth + 1,
                            'path' => $itemPath,
                        ])
                    </ul>
                </div>
            @endif
        </li>
    @endcanany
@endforeach
