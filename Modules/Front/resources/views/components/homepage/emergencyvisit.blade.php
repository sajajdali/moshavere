<div>
    @if (isset($fetchData['EmergencyDoctors']) && $fetchData['EmergencyDoctors']->isNotEmpty())
        <section class="py-12">
            <div class="container space-y-8">
                <div class="flex flex-col md:flex-row itemsstart md:items-center justify-between gap-4">
                    <div class="space-y-2">
                        <h3 class="font-bold text-xl text-green">ویزیت فوری پزشک</h3>
                        <p class="text-secondary-500">ارتباط با پزشکانی که در سریع‌ترین زمان امکان پاسخگویی دارند</p>
                    </div>
                    <a href="{{route('front.searchPage',['query' => 'پزشکان'])}}" class="flex items-center gap-4 text-primary-main">
                        <span class="font-semibold">مشاهده همه</span>
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="#sprite-arrow-left" />
                        </svg>
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($fetchData['EmergencyDoctors'] as $emDoc)
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
                            <a href="{{ route('front.doctor.profile', ['doctor_id' => $emDoc->id]) }}"
                                class="btn__green">دریافت نوبت</a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
