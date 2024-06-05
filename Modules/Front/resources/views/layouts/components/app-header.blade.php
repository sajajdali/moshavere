<div>
    <div class="hidden">
    {!! file_get_contents(public_path('assets/front/assets/svg/icon.svg')) !!}
    </div>
    <nav class="navbar">
        <div class="navbar__container">
            <a href="#">
                <img src="{{setting(Modules\Setting\Enum\SettingKeyEnum::SITE_LOGO_URL)}}" class="w-[100px]" />
            </a>
            <ul class="navbar__menu">
                <li class="navbar__menu-item">
                    <a href="#">صفحه اصلی</a>
                </li>
                <li class="navbar__menu-item">
                    <a href="#">درباره ما</a>
                </li>
                <li class="navbar__menu-item">
                    <a href="#">تماس با ما</a>
                </li>
                <li class="navbar__menu-item">
                    <a href="#">لیست پزشکان</a>
                </li>
                <li class="navbar__menu-item">
                    <a href="#">ثبت شکایات</a>
                </li>
            </ul>
            <a href="#" class="!hidden md:!flex btn__blue--round-full">
                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="#sprite-user" />
                </svg>
                <p>ورود</p>
            </a>
            <button type="button" class="block md:hidden navbar__menu--button">
                <svg class="w-7 h-7" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="#sprite-menu" />
                </svg>
            </button>
        </div>
    </nav>


</div>
