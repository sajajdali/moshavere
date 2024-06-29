<div>
    <section class="py-12 bg-secondary-100">
        <div class="container space-y-8">
            <div class="flex flex-row items-center justify-between gap-3">
                <h3 class="font-bold text-xl">سوالات متداول</h3>
                <a href="#" class="flex items-center gap-4 text-primary-main">
                    <span class="font-semibold">ارتباط با پشتیبانی</span>
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="../assets/svg/icon.svg#sprite-arrow-left" />
                    </svg>
                </a>
            </div>
            <div class="bg-white p-4 rounded-xl flex flex-col gap-6">
                @foreach ($fetchData['faqs'] as $faq)
                    <div class="flex flex-col gap-3">
                        <div class="accordion__container accordion_select__container">
                            <div class="accordion_select__button">
                                <p class="accordion_select__text">
                                    {{ $faq->question }}
                                </p>
                                <div class="accordion_select__icon">
                                    <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="../assets/svg/icon.svg#sprite-chevron-down-circle" />
                                    </svg>
                                </div>
                            </div>
                            <div class="accordion_select__content">
                                <p class="text-sm leading-[1.65rem] text-justify text-gray-700">
                                    {{ $faq->answer }}
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
                                        <use xlink:href="../assets/svg/icon.svg#sprite-chevron-down-circle" />
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
                @endforeach
                <div class="flex flex-col md:flex-row gap-3 items-center justify-between">
                    <p class="text-sm">
                        در صورتیکه در یافتن پاسخ به مشکل خوردید یا آن را از بین سوالات متداول نیافته‌اید، با پشتیبانی
                        ارتباط
                        برقرار کنید.
                    </p>
                    <a href="{{route('front.contactUs')}}"
                        class="w-full md:w-auto text-center block bg-secondary-100 hover:bg-secondary-200 transition-colors rounded-xl py-1 px-5">
                        ارتباط پشتیبانی
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
