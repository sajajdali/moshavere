@if(isset($fetchData['introductionDoctors']) && $fetchData['introductionDoctors']->isNotEmpty() )
    <div>
        <section class="pb-12">
            <div class="container space-y-8">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
                    <div class="space-y-2">
                        <h3 class="font-bold text-xl">معرفی پزشکان</h3>
                        <p class="text-secondary-500">ارتباط با پزشکانی که در سریع‌ترین زمان امکان پاسخگویی دارند</p>
                    </div>
                    <a href="#" class="flex items-center gap-4 text-primary-main">
                        <span class="font-semibold">مشاهده همه</span>
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="../assets/svg/icon.svg#sprite-arrow-left" />
                        </svg>
                    </a>
                </div>
                <div class="swiper swiper-cards">
                    <div class="swiper-wrapper">
                        @foreach ($fetchData['introductionDoctors'] as $introDoc)
                            <div class="swiper-slide">
                                <article role="banner"
                                    class="border-[3px] border-secondary-200 rounded-xl p-5 flex flex-col gap-5">
                                    <div class="flex flex-col items-center gap-7">
                                        <div class="relative">
                                            <span
                                                class="absolute top-0 right-0 block w-4 h-4 bg-green border-[3px] border-white rounded-full"></span>
                                            <div
                                                class="w-[60px] h-[60px] overflow-hidden rounded-full flex items-center justify-center">
                                                <img src="{{ $introDoc->avatar }}" alt="doctor-image"
                                                    class="w-full h-full object-cover" />
                                            </div>
                                        </div>
                                        <div class="text-center space-y-1">
                                            <h4 class="font-bold text-lg">دکتر {{ $introDoc->full_name }}</h4>
                                            <p class="text-secondary-400">{{ $introDoc->DocSpecialities() }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                                <use xlink:href="#sprite-location" />
                                            </svg>
                                            <span>{{ $introDoc->DocProvinces() }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span>4.5</span>
                                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                                <use xlink:href="#sprite-star-full" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between text-secondary-400">
                                        <p>زمان انتظار</p>
                                        <p>{{ $introDoc->dr_waiting_time }}</p>
                                    </div>
                                    <a href="{{ route('front.doctor.profile', ['doctor_id' => $introDoc->id]) }}"
                                        class="btn__blue--tint">دریافت نوبت</a>
                                </article>
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
                </div>
            </div>
        </section>
    </div>
@endif
