<div>
    <main class="py-10 md:py-16 bg-secondary-100">
        <section class="container flex flex-col md:flex-row gap-10">
            <section class="basis-full md:basis-[60%] flex flex-col gap-6">
                <div class="bg-white rounded-lg space-y-4 p-4">
                    <header class="flex flex-col sm:flex-row items-center justify-between gap-3">
                        <p class="text-lg font-semibold">اطلاعات تخصصی پزشک</p>
                        <div class="bg-green text-white rounded-full py-2 px-5">
                            89% رضایت مراجعین
                        </div>
                    </header>
                    <main class="bg-secondary-100 rounded-lg p-4 flex flex-col sm:flex-row items-center gap-5">
                        <div
                            class="w-[70px] h-[70px] overflow-hidden rounded-full flex items-center justify-center border-2 border-white ring-2 ring-blue-sky">
                            <img src="{{ $doc->avatar }}" alt="doctor-image-name" />
                        </div>
                        <div
                            class="w-[calc(100%-70px-1.25rem)] flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="space-y-3 text-center sm:text-right">
                                <p class="text-lg font-bold">دکتر {{ $doc->full_name }}</p>
                                <p class="bg-secondary-200 rounded-lg py-2 px-3 text-sm">
                                    {{ $doc->DocSpecialities() }}
                                </p>
                            </div>
                            <div class="flex flex-col items-center sm:items-end gap-4">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-flag" />
                                    </svg>
                                    <p>{{ $doc->dr_display_experince }}</p>
                                </div>
                                <p class="text-secondary-400 text-sm">شماره نظام پزشکی: {{ $doc->dr_licence_number }}
                                </p>
                            </div>
                        </div>
                    </main>
                    <footer class="flex flex-col sm:flex-row items-center justify-between text-secondary-400 gap-4">
                        <div class="flex items-center gap-2">
                        </div>
                        <div class="flex items-center text-sm gap-4">
                            <button type="button" class="flex items-center gap-2">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                    <use xlink:href="#sprite-save" />
                                </svg>
                                <p>نشان</p>
                            </button>
                            <button type="button" class="flex items-center gap-2">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                    <use xlink:href="#sprite-share" />
                                </svg>
                                <p>اشتراک گذاری</p>
                            </button>
                            <button type="button" class="flex items-center gap-2">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                    <use xlink:href="#sprite-info" />
                                </svg>
                                <p>گزارش خطا</p>
                            </button>
                        </div>
                    </footer>
                </div>

                @if (isset($doc->dr_display_discription))
                    <div class="bg-white rounded-lg px-4 divide-y divide-secondary-200">
                        <div class="py-4 flex items-center gap-3">
                            <svg class="w-5 h-5 text-secondary-300" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="#sprite-question" />
                            </svg>
                            <p class="w-[calc(100%-2rem)]">
                                {{ $doc->dr_display_discription }}
                            </p>
                        </div>
                    </div>
                @endif
                <!-- container -->
                <div class="flex flex-col gap-2">
                    <div class="bg-white rounded-t-lg space-y-4 p-4">
                        <header class="flex flex-col sm:flex-row items-center justify-between gap-3">
                            <p class="text-lg font-semibold">اطلاعات تخصصی پزشک</p>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-3 bg-green text-white rounded-full py-2 px-5">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-heart" />
                                    </svg>
                                    <p>89% رضایت</p>
                                </div>
                                <p>از 200 نفر</p>
                            </div>
                        </header>
                        <main class="flex flex-col gap-3">
                            <div class="border-2 border-secondary-200 p-4 rounded-lg flex flex-col gap-3">
                                <div class="flex items-center justify-between">
                                    <p>برخورد مناسب</p>
                                    <div class="flex items-center gap-2 font-bold">
                                        <p>5</p>
                                        <p class="text-xl">/</p>
                                        <p class="text-green">4.5</p>
                                    </div>
                                </div>
                                <progress dir="ltr" max="100" value="80"></progress>
                            </div>
                            <div class="border-2 border-secondary-200 p-4 rounded-lg flex flex-col gap-3">
                                <div class="flex items-center justify-between">
                                    <p>توضیحات مفید پزشک</p>
                                    <div class="flex items-center gap-2 font-bold">
                                        <p>5</p>
                                        <p class="text-xl">/</p>
                                        <p class="text-green">4.5</p>
                                    </div>
                                </div>
                                <progress dir="ltr" max="100" value="80"></progress>
                            </div>
                            <div class="border-2 border-secondary-200 p-4 rounded-lg flex flex-col gap-3">
                                <div class="flex items-center justify-between">
                                    <p>مهارت و تخصص</p>
                                    <div class="flex items-center gap-2 font-bold">
                                        <p>5</p>
                                        <p class="text-xl">/</p>
                                        <p class="text-green">4.5</p>
                                    </div>
                                </div>
                                <progress dir="ltr" max="100" value="80"></progress>
                            </div>
                        </main>
                        <footer class="flex flex-col sm:flex-row items-center justify-between text-secondary-400 gap-4">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                    <use xlink:href="#sprite-eye" />
                                </svg>
                                <p>13 هزار بار مشاهده</p>
                            </div>
                            <div class="flex items-center text-sm gap-4">
                                <button type="button" class="flex items-center gap-2">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-save" />
                                    </svg>
                                    <p>نشان</p>
                                </button>
                                <button type="button" class="flex items-center gap-2">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-share" />
                                    </svg>
                                    <p>اشتراک گذاری</p>
                                </button>
                                <button type="button" class="flex items-center gap-2">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-info" />
                                    </svg>
                                    <p>گزارش خطا</p>
                                </button>
                            </div>
                        </footer>
                    </div>
                    <div class="bg-white p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <p class="font-bold">نظرات خود را با دیگران به اشتراک بگذارید</p>
                        <a href="#" class="btn__blue--round-full-outline font-semibold">
                            ثبت نظر
                        </a>
                    </div>
                    @if ($fetchData['comments']->isNotEmpty())
                        <div class="bg-white rounded-b-lg p-4 space-y-4">
                            <header>
                                <div class="flex items-center">
                                    <p class="">نظرات</p>
                                </div>
                            </header>
                            @foreach ($fetchData['comments'] as $key => $comment)
                                <main class="flex flex-col gap-3">
                                    <div class="border-2 border-secondary-200 rounded-lg flex flex-col gap-4 p-4">
                                        <div class="flex flex-col sm:flex-row items-start justify-between gap-3">
                                            <div class="flex items-center gap-4">
                                                <div
                                                    class="w-[65px] h-[65px] rounded-full flex items-center justify-center bg-primary-main text-white font-bold text-2xl">
                                                    ن</div>
                                                <div class="w-[calc(100%-65px-0.75rem)] space-y-2">
                                                    <p>{{ $comment->user->full_name }}</p>
                                                    <div class="flex items-center gap-3 text-sm text-secondary-400">
                                                        <div
                                                            class="hidden sm:block py-1 px-3 bg-secondary-100 rounded-full">
                                                            <p>{{ verta($comment->created_at)->formatDifference() }}</p>
                                                        </div>
                                                        <div class="w-[1px] h-3 bg-secondary-400"></div>
                                                        <p>{{ $comment->doctor->dr_display_address }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                class="flex items-center gap-2 bg-green/20 text-green rounded-full py-1 px-4 text-sm font-bold">
                                                <p class="w-[calc(100%-1.75rem)]">{{ $comment->star }}</p>
                                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                                    <use xlink:href="#sprite-star-full" />
                                                </svg>
                                            </div>
                                        </div>
                                        <p class="leading-7">{{ $comment->body }}</p>
                                        <div class="flex flex-col md:flex-row gap-3">
                                            <form action=""
                                                class="flex-grow relative flex items-center border-2 border-secondary-200 rounded-lg px-3 h-[40px]">
                                                <input type="text" class="flex-grow border-none outline-none"
                                                    placeholder="پاسخ شما" />
                                                <button class="flex items-center gap-2 font-bold">
                                                    <p>ارسال</p>
                                                    <svg class="w-5 h-5 mt-[2px]" xmlns="http://www.w3.org/2000/svg">
                                                        <use xlink:href="#sprite-chevron-left-circle" />
                                                    </svg>
                                                </button>
                                            </form>
                                            <div class="flex gap-3">
                                                <button
                                                    class="flex-grow flex items-center gap-2 border-2 border-secondary-200 rounded-lg px-3 font-bold h-[40px]">
                                                    <p>مفید بود</p>
                                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                                        <use xlink:href="#sprite-emoji" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </main>
                            @endforeach
                            <button type="button" class="w-full py-2 px-5 flex items-center justify-center gap-3">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                    <use xlink:href="#sprite-eye" />
                                </svg>
                                <p class="font-bold">نمایش بیشتر</p>
                            </button>
                        </div>
                    @endif

                </div>
                <!-- end container -->

                <!-- breadcrumb -->
                <div class="flex flex-wrap items-center gap-2 text-secondary-400">
                    <a href="#" class="hover:text-black">سلامت شرق</a>
                    <svg class="w-7 h-7" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="#sprite-chevron-left" />
                    </svg>
                    <a href="#" class="hover:text-black">پزشکان و مراکز درمانی</a>
                    <svg class="w-7 h-7" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="#sprite-chevron-left" />
                    </svg>
                    <a href="#" class="hover:text-black">متخصص بی‌هوشی و مراقبت‌های ویژه</a>
                    <svg class="w-7 h-7" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="#sprite-chevron-left" />
                    </svg>
                    <span>هادی مسلم</span>
                </div>
                <!-- end breadcrumb -->
                @isset($doc->dr_biography)
                    <div class="bg-white p-4 space-y-4 rounded-lg">
                        <header>
                            <h4 class="text-lg font-bold">درباره پزشک</h4>
                        </header>
                        <main class="bg-secondary-100 rounded-lg p-4 space-y-4">
                            <p>
                                {!! nl2br($doc->dr_biography) !!}
                            </p>
                            <div class="flex items-center justify-between gap-3">
                                <p class="bg-secondary-200 rounded-lg py-2 px-3 text-sm">
                                    {{ $doc->DocSpecialities() }}
                                </p>
                                <p class="text-secondary-400 text-sm">شماره نظام پزشکی: {{ $doc->dr_licence_number }}</p>
                            </div>
                        </main>
                        <footer class="flex flex-col sm:flex-row items-center justify-between text-secondary-400 gap-3">
                            <div class="flex items-center gap-2">

                            </div>
                            <div class="flex items-center text-sm gap-4">
                                <button type="button" class="flex items-center gap-2">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-save" />
                                    </svg>
                                    <p>نشان</p>
                                </button>
                                <button type="button" class="flex items-center gap-2">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-share" />
                                    </svg>
                                    <p>اشتراک گذاری</p>
                                </button>
                                <button type="button" class="flex items-center gap-2">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-info" />
                                    </svg>
                                    <p>گزارش خطا</p>
                                </button>
                            </div>
                        </footer>
                    </div>
                @endisset

                {{-- <div class="bg-white p-4 flex flex-col gap-4 rounded-lg">
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
                                    لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان
                                    گرافیک است
                                    چاپگرها
                                    و متون
                                    بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است و برای شرایط فعلی تکنولوژی مورد
                                    نیاز و
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
                                    لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان
                                    گرافیک است
                                    چاپگرها
                                    و متون
                                    بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است و برای شرایط فعلی تکنولوژی مورد
                                    نیاز و
                                    کاربردهای متنوع
                                    با هدف بهبود ابزارهای کاربردی می باشد
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row gap-3 items-center justify-between">
                        <p class="text-sm">
                            در صورتیکه در یافتن پاسخ به مشکل خوردید یا آن را از بین سوالات متداول نیافته‌اید، با
                            پشتیبانی ارتباط
                            برقرار کنید.
                        </p>
                        <a href="#"
                            class="w-full md:w-auto text-center block bg-secondary-100 hover:bg-secondary-200 transition-colors rounded-xl py-1 px-5">
                            ارتباط پشتیبانی
                        </a>
                    </div>
                </div> --}}
            </section>
            <section class="basis-full md:basis-[40%] flex flex-col gap-6">
                <div class="bg-white rounded-lg p-4 space-y-4">
                    <header>
                        <p class="font-bold">
                            دریافت نوبت اینترنتی
                        </p>
                    </header>
                    <main class="flex flex-col gap-3">
                        <div class="text-sm border-2 border-secondary-200 p-3 rounded-lg space-y-3">
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                                <p class="font-bold">{{ $doc->dr_display_address }}</p>
                                <div class="flex gap-3">
                                    <div class="bg-secondary-100 text-black rounded-lg py-2 px-5 text-sm">
                                        تماس
                                    </div>
                                    <div class="bg-secondary-100 text-black rounded-lg py-2 px-5 text-sm">
                                        مسیریابی
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <svg class="w-5 h-5 mt-[2px]" xmlns="http://www.w3.org/2000/svg">
                                    <use xlink:href="#sprite-location" />
                                </svg>
                                <p class="w-[calc(100%-2rem)]">
                                    خراسان رضوی، مشهد، احمد آباد، بولوار ملاصدرا، بولوار بعثت ، بین بعثت 1 و 3 ، ساختمان
                                    شماره 11
                                </p>
                            </div>
                            <div class="flex gap-3">
                                <svg class="w-5 h-5 mt-[2px]" xmlns="http://www.w3.org/2000/svg">
                                    <use xlink:href="#sprite-date" />
                                </svg>
                                <p class="w-[calc(100%-2rem)]">
                                    نزدیک ترین نوبت خالی: یکشنبه 12 فروردین، ساعت 16
                                </p>
                            </div>
                        </div>

                        <button type="submit" @if (isset($doc->ban_user) && $doc->ban_user == false) disabled @endif
                            class="btn__blue--round-full-between">
                            <p>دریافت نوبت دکتر {{ $doc->full_name }}</p>
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="#sprite-arrow-left-circle" />
                            </svg>
                        </button>
                        @if (isset($doc->ban_user) && $doc->ban_user == true)
                            <div class="error_badge">
                                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                    <use xlink:href="#sprite-warning" />
                                </svg>
                                <p>
                                    هم اکنون نوبت دهی برای این پزشک محدود شده است.
                                </p>
                            </div>
                        @endif
                    </main>
                </div>
                {{-- <div class="warning_badge">
                    <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="#sprite-warning" />
                    </svg>
                    <p>مراجعین محترم نوبت های ویزیت اسفند ماه پر شده است لطفا جهت دریافت نوبت
                        برای فروردین ماه از۲۷اسفند ساعت ۱۲شب به بعد به سایت مراجعه کنید</p>
                </div> --}}
                <div class="bg-white p-4 flex flex-col gap-4">
                    <p class="font-bold">
                        راه‌های ارتباطی با پزشک
                    </p>
                    <div class="border-2 border-secondary-200 rounded-lg flex flex-col p-4 gap-3">
                        <div class="flex items-center justify-between text-sm">
                            <p>
                                شماره تلفن: <span dir="ltr">{{ $doc->dr_display_mobile }}</span>
                            </p>
                            <a href="#" class="bg-secondary-100 rounded-lg py-2 px-4">
                                تماس
                            </a>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <p>
                                {{ $doc->dr_display_address }}
                            </p>
                            <a href="#" class="bg-secondary-100 rounded-lg py-2 px-4">
                                مسیریابی به مطب
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </section>
    </main>
</div>
