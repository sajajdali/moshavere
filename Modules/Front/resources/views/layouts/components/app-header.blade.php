<div>
    <div class="hidden">
        {!! file_get_contents(public_path('assets/front/assets/svg/icon.svg')) !!}
    </div>
    <nav class="navbar">
        <div class="navbar__container">
            @unless (disableUi())
                <a href="{{ route('front.homePage') }}">
                    <img src="{{ assetStorage(settingVfc($settingValues,Modules\Setting\Enum\SettingKeyEnum::SITE_LOGO_URL)) }}"
                        class="max-h-[70px] h-auto w-auto" />
                </a>
                <ul class="navbar__menu">
                    <li class="navbar__menu-item">
                        <a href="{{ route('front.homePage') }}">صفحه اصلی</a>
                    </li>
                    @unless (disableUi())

                        {{--                <li class="navbar__menu-item"> --}}
                        {{--                    <a href="{{ route('front.aboutUs') }}">درباره ما</a> --}}
                        {{--                </li> --}}
                        {{--                <li class="navbar__menu-item"> --}}
                        {{--                    <a href="{{ route('front.contactUs') }}">درخواست مشاوره</a> --}}
                        {{--                </li> --}}
                        @if (settingVfc($settingValues, Modules\Setting\Enum\SettingKeyEnum::ENABLE_DOCTORS_MENU))
                            <li class="navbar__menu-item">
                                <a href="{{ route('front.searchPage', ['query' => 'پزشکان']) }}">لیست پزشکان</a>
                            </li>
                        @endif
                        @if (settingVfc($settingValues, Modules\Setting\Enum\SettingKeyEnum::ENABLE_CONTACT_US_MENU))
                            <li class="navbar__menu-item">
                                <a href="{{ route('front.contactUs') }}">ارتباط با ما</a>
                            </li>
                        @endif
                        @if (settingVfc($settingValues, Modules\Setting\Enum\SettingKeyEnum::SHOW_ABOUT_US_MENU_BUTTON))
                            <li class="navbar__menu-item">
                                <a href="{{ route('front.aboutUs') }}">درباره ما</a>
                            </li>
                        @endif
                    @endunless
                </ul>
                <div x-data="{ open: false }" class="relative">
                    @auth
                        <button @click="open = !open" class="!hidden md:!flex btn__blue--round-full items-center">
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="#sprite-user" />
                            </svg>
                            <p>{{ auth()->user()->full_name }}</p>
                        </button>
                        <div x-show="open" @click.away="open = false"
                            class="absolute border-bottom border-gray-100 z-50 right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1">
                            @if (auth()->user()->can('ADMIN_ACCESS'))
                                <a href="{{ route('admin.dashboard') }}"
                                    class="block px-4 py-2 text-gray-800 hover:bg-gray-100">پنل مدیریت</a>
                            @else
                                <a href="{{ route('front.user.profile') }}"
                                    class="block px-4 py-2 text-gray-800 hover:bg-gray-100">مشاهده پروفایل</a>
                            @endif
                            <a href="{{ route('front.logout') }}"
                                class="w-full block px-4 py-2 text-gray-800 hover:bg-gray-100">خروج</a>
                        </div>
                    @else
                        <a href="{{ route('front.login.user') }}" class="!hidden md:!flex btn__blue--round-full items-center">
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="#sprite-user" />
                            </svg>
                            <p>ورود</p>
                        </a>
                    @endauth
                </div>

                <button type="button" class="block md:hidden navbar__menu--button">
                    <svg class="w-7 h-7" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="#sprite-menu" />
                    </svg>
                </button>
            </div>
        </nav>
    @endunless

</div>
