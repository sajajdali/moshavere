<div>
    <!-- header -->
    <header>
        <section class="bg-primary-main p-4">
            <h1 class="font-semibold text-center text-2xl text-white">
                درباره ما
            </h1>
        </section>
        <section class="bg-secondary-100">
            <div class="container py-10 flex flex-col gap-3">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
                    <h2 class="font-semibold text-lg">
                        {{ setting(\Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_FIRST_SECTION_TITLE) }}
                    </h2>
                    <a href="{{route('front.searchPage',['query' => 'پزشکان'])}}" class="flex items-center gap-4 text-primary-main">
                        <span class="font-semibold">لیست پزشکان</span>
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="#sprite-arrow-left" />
                        </svg>
                    </a>
                </div>
                <p class="leading-7 text-secondary-400">
                    {{ setting(\Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_FIRST_SECTION_DESCRIPTION) }}
                </p>
            </div>
        </section>
    </header>

    <!-- first section -->
    <section class="container space-y-16 py-16">
        <section class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <div class="flex flex-col items-start gap-4">
                <h3 class="font-semibold text-xl">
                    {{ setting(\Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_SECEND_SECTION_TITLE) }}
                </h3>
                <p class="leading-7 text-secondary-400">
                    {{ setting(\Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_SECEND_SECTION_DESCRIPTION) }}
                </p>
                <a href="{{route('front.searchPage',['query' => 'پزشکان'])}}" class="btn__blue--round-full">
                    <span>لیست پزشکان برتر</span>
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="#sprite-chevron-left-circle" />
                    </svg>
                </a>
            </div>
            <div class="h-[300px] overflow-hidden rounded-lg">
                <img src="{{ setting(\Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_SECEND_SECTION_IMAGE) }}"
                    alt="about doctors" class="w-full h-full object-cover" />
            </div>
        </section>
        <section class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <div class="h-[300px] overflow-hidden rounded-lg">
                <img src="{{ setting(\Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_THIRD_SECTION_IMAGE) }}"
                    alt="about doctors" class="w-full h-full object-cover" />
            </div>
            <div class="flex flex-col items-start gap-4">
                <h3 class="font-semibold text-xl">
                    {{ setting(\Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_THIRD_SECTION_TITLE) }}
                </h3>
                <p class="leading-7 text-secondary-400">
                    {{ setting(\Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_THIRD_SECTION_DESCRIPTION) }}
                </p>
                <a href="{{route('front.searchPage',['query' => 'پزشکان'])}}" class="btn__blue--round-full">
                    <span>لیست پزشکان برتر</span>
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="#sprite-chevron-left-circle" />
                    </svg>
                </a>
            </div>
        </section>
    </section>

    {{-- نظر مخاطبین --}}
    @if (isset($fetchData['comments']) && $fetchData['comments']->isNotEmpty())
        @include('front::components.homepage.comments')
    @endif

    <!-- fourth section -->
    <section class="container py-16 grid grid-cols-1 md:grid-cols-2 gap-12">
        <div class="flex flex-col gap-4">
            <h3 class="font-semibold text-xl">
                {{ setting(\Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_FOURTH_SECTION_TITLE) }}
            </h3>
            <p class="leading-7 text-secondary-400">
                {{ setting(\Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_FOURTH_SECTION_TITLE) }}
            </p>
            <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-5">

            </div>
            <div class="flex">
                <a href="{{route('front.searchPage',['query' => 'پزشکان'])}}" class="btn__blue--round-full">
                    <span>لیست پزشکان برتر</span>
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="#sprite-chevron-left-circle" />
                    </svg>
                </a>
            </div>
        </div>
        <div class="-translate-x-1 relative">
            <div class="w-full h-[400px] rounded-lg bg-primary-main"></div>
            <div class="absolute left-4 top-4 w-full h-[400px] overflow-hidden rounded-lg">
                <img src="{{ setting(\Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_FOURTH_SECTION_IMAGE) }}" alt="about doctors" class="w-full h-full object-cover" />
            </div>
        </div>
    </section>


    <!-- top doctors -->
    @include('front::components.homepage.emergencyvisit')

    <!-- FAQ -->
    @include('front::components.homepage.faq')
    {{-- <section class="py-12 bg-secondary-100">
        <div class="container space-y-8">
            <div class="flex flex-row items-center justify-between gap-3">
                <h3 class="font-bold text-xl">سوالات متداول</h3>
                <a href="#" class="flex items-center gap-4 text-primary-main">
                    <span class="font-semibold">ارتباط با پشتیبانی</span>
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="#sprite-arrow-left" />
                    </svg>
                </a>
            </div>
            <div class="bg-white p-4 rounded-xl flex flex-col gap-6">
                <p class="text-lg">درباره پزشک</p>
                <div class="flex flex-col gap-3">
                    <div class="accordion__container accordion_select__container">
                        <div class="accordion_select__button">
                            <p class="accordion_select__text">
                                بعد از خرید چه مدت طول می کشد تا خدمات به من ارائه شوند؟
                            </p>
                            <div class="accordion_select__icon">
                                <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg">
                                    <use xlink:href="#sprite-chevron-down-circle" />
                                </svg>
                            </div>
                        </div>
                        <div class="accordion_select__content">
                            <p class="text-sm leading-[1.65rem] text-justify text-gray-700">
                                لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان گرافیک
                                است
                                چاپگرها
                                و متون
                                بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است و برای شرایط فعلی تکنولوژی مورد نیاز
                                و
                                کاربردهای متنوع
                                با هدف بهبود ابزارهای کاربردی می باشد
                            </p>
                        </div>
                    </div>
                    <div class="accordion__container accordion_select__container">
                        <div class="accordion_select__button">
                            <p class="accordion_select__text">
                                بعد از خرید چه مدت طول می کشد تا خدمات به من ارائه شوند؟
                            </p>
                            <div class="accordion_select__icon">
                                <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg">
                                    <use xlink:href="#sprite-chevron-down-circle" />
                                </svg>
                            </div>
                        </div>
                        <div class="accordion_select__content">
                            <p class="text-sm leading-[1.65rem] text-justify text-gray-700">
                                لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان گرافیک
                                است
                                چاپگرها
                                و متون
                                بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است و برای شرایط فعلی تکنولوژی مورد نیاز
                                و
                                کاربردهای متنوع
                                با هدف بهبود ابزارهای کاربردی می باشد
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row gap-3 items-center justify-between">
                    <p class="text-sm">
                        در صورتیکه در یافتن پاسخ به مشکل خوردید یا آن را از بین سوالات متداول نیافته‌اید، با پشتیبانی
                        ارتباط
                        برقرار کنید.
                    </p>
                    <a href="#"
                        class="w-full md:w-auto text-center block bg-secondary-100 hover:bg-secondary-200 transition-colors rounded-xl py-1 px-5">
                        ارتباط پشتیبانی
                    </a>
                </div>
            </div>
        </div>
    </section> --}}
</div>
