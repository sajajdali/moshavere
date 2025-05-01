<!-- footer -->
<footer>
    @unless (disableUi())
        <section class="bg-secondary-200 py-8">
            <div class="container grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="space-y-4">
                    <img src="{{ assetStorage(setting(Modules\Setting\Enum\SettingKeyEnum::SITE_LOGO_URL)) }}"
                         class="w-[100px] mx-auto"/>
                    <p>
                        {{ setting(Modules\Setting\Enum\SettingKeyEnum::FOOTER_DESCRIPTION) }}
                    </p>
                    <ul class="flex items-center gap-3">
                        @if( setting(Modules\Setting\Enum\SettingKeyEnum::INSTAGRAM_ADDRESS))
                            <li>
                                <a href="{{ setting(Modules\Setting\Enum\SettingKeyEnum::INSTAGRAM_ADDRESS) }}"
                                   class="bg-white rounded-full w-[35px] h-[35px] flex items-center justify-center text-black">
                                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-instagram"/>
                                    </svg>
                                </a>
                            </li>
                        @endif
                        @if( setting(Modules\Setting\Enum\SettingKeyEnum::TELEGRAM_ADDRESS))
                            <li>
                                <a href="{{ setting(Modules\Setting\Enum\SettingKeyEnum::TELEGRAM_ADDRESS) }}"
                                   class="bg-white rounded-full w-[35px] h-[35px] flex items-center justify-center text-black">
                                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-telegram"/>
                                    </svg>
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
                <div class="space-y-5 mr-10">
                    <p class="font-semibold">صفحات اصلی</p>
                    <ul class="text-sm space-y-4">
                        <li>
                            <a href="{{ route('front.homePage') }}">صفحه اصلی</a>
                        </li>
                        @if(setting(\Modules\Setting\Enum\SettingKeyEnum::ENABLE_CONTACT_US_MENU))
                            <li>
                                <a href="{{ route('front.contactUs') }}">تماس با ما</a>
                            </li>
                        @endif
                        @if(setting(\Modules\Setting\Enum\SettingKeyEnum::ENABLE_DOCTORS_MENU))
                            <li>
                                <a href=""{{ route('front.searchPage', ['query' => 'پزشکان']) }}">لیست پزشکان</a>
                            </li>
                        @endif

                    </ul>
                </div>
                <div class="space-y-2">

                </div>
                <div class="space-y-6">
                    <p class="font-semibold">نماد ها</p>
                    <div class="grid grid-cols-2 gap-8">
                        @if(setting(\Modules\Setting\Enum\SettingKeyEnum::FOOTER_ENAMAD))
                            <a referrerpolicy='origin' target='_blank'
                               href='{{setting(\Modules\Setting\Enum\SettingKeyEnum::FOOTER_ENAMAD)}}'
                            ><img class="h-[114px]"
                                  referrerpolicy='origin'
                                  src={{front_asset('img/enamad.png')}}
                                   alt='' style='cursor:pointer'></a>
                        @endif
                        {{-- <a href='https://trustseal.enamad.ir/?id=524685&Code=XbRtaf8YeLbNzaEfJdrxZzyGgINAyaCR'
                            referrerpolicy='origin' target='_blank' class="flex justify-center">
                            <img src="{{ front_asset('assets/images/enamad.png') }}" class="h-[114px]" />
                        </a> --}}
                        @if(setting(\Modules\Setting\Enum\SettingKeyEnum::FOOTER_SAMANDEHI))
                            <a href="{{setting(\Modules\Setting\Enum\SettingKeyEnum::FOOTER_SAMANDEHI)}}"
                               class="flex justify-center">
                                <img src="{{ front_asset('assets/images/samandehi.png') }}" class="h-[114px]"/>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endunless
    @if(!disableUi())
        <section class="bg-secondary-300 py-3 px-4">
            <p class="text-sm text-center">
                تمامی حقوق مادی و معنوی این وب‌سایت، خدمات و محتوای مربوط به آن متعلق به سایت نوبت دهی میباشد.
            </p>
        </section>
    @else
        <section class="bg-secondary-300 py-3 px-4">
            <p class="text-sm text-center">
                تمامی حقوق مادی و معنوی این وب‌سایت، خدمات و محتوای مربوط به آن متعلق به سایت نوبت دهی میباشد.
            </p>
        </section>
    @endif
</footer>
