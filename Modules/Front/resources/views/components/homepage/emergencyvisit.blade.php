<div>
    @if (isset($fetchData['EmergencyDoctors']) && $fetchData['EmergencyDoctors']->isNotEmpty())
        <section class="py-12">
            <div class="container space-y-8">
                <div class="flex flex-col md:flex-row itemsstart md:items-center justify-between gap-4">
                    <div class="space-y-2">
                        <h3 class="font-bold text-xl text-green">
                            {{ setting(\Modules\Setting\Enum\SettingKeyEnum::SITE_FIRST_SECTION_TITLE) ?? 'ویزیت فوری پزشک' }}
                        </h3>
                        <p class="text-secondary-500">
                            {{ setting(\Modules\Setting\Enum\SettingKeyEnum::SITE_FIRST_SECTION_DESCRIPTION) ?? 'ارتباط با پزشکانی که در سریع‌ترین زمان امکان پاسخگویی دارند' }}
                        </p>
                    </div>
                    <a href="{{ route('front.searchPage', ['query' => 'پزشکان']) }}"
                        class="flex items-center gap-4 text-primary-main">
                        <span class="font-semibold">مشاهده همه</span>
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="#sprite-arrow-left" />
                        </svg>
                    </a>
                </div>
                {{-- <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"> --}}
                <div class="swiper swiper-cards ">
                    <div class="swiper-wrapper grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($fetchData['EmergencyDoctors'] as $emDoc)
                            <div class="swiper-slide">
                                <article role="banner"
                                    class="bg-green/10 border-4 border-green/30 rounded-xl p-5 flex flex-col gap-5">
                                    <div class="flex flex-col items-center gap-7">
                                        <div class="relative">
                                            <span
                                                class="absolute top-0 right-0 block w-4 h-4 bg-green border-[3px] border-white rounded-full"></span>
                                            <div
                                                class="w-[60px] h-[60px] overflow-hidden rounded-full flex items-center justify-center">
                                                <img src="{{ $emDoc->avatar }}" alt="doctor-image"
                                                    class="w-full h-full object-cover" />
                                            </div>
                                        </div>
                                        <div class="text-center space-y-1">
                                            <h4 class="font-bold text-lg">دکتر {{ $emDoc->full_name }}</h4>
                                            <p class="text-secondary-400">{{ $emDoc->DocSpecialities() }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                                <use xlink:href="#sprite-location" />
                                            </svg>
                                            {{-- TODO::load province --}}
                                            <span>{{ $emDoc->DocProvinces() }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            {{-- TODO::load rate from feedBack --}}
                                            <span>4.5</span>
                                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                                <use xlink:href="#sprite-star-full" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between text-secondary-400">
                                        <p>زمان انتظار</p>
                                        <p>{{ $emDoc->dr_waiting_time }}</p>
                                    </div>
                                    <button wire:click='docpage({{ $emDoc->id }})' type="button" class="btn__green">
                                        <div role="status" wire:loading wire:target='docpage({{ $emDoc->id }})'>
                                            <svg aria-hidden="true"
                                                class="w-5 h-5 text-white-200 animate-spin dark:text-white-600 fill-gray-700"
                                                viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                                    fill="currentFill" />
                                            </svg>
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        <span wire:loading.remove wire:target="docpage({{ $emDoc->id }})">دریافت
                                            نوبت</span>
                                    </button>
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
    @endif
</div>
