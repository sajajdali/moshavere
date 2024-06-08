<div>
    <!-- newest list of doctor -->
    <section class="py-12 bg-secondary-100">
        <div class="container space-y-8">
            <div class="flex flex-row items-center justify-between gap-3">
                <h3 class="font-bold text-xl">جدیدترین پزشکان</h3>
                <a href="#" class="flex items-center gap-4 text-primary-main">
                    <span class="font-semibold">مشاهده همه</span>
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="../assets/svg/icon.svg#sprite-arrow-left" />
                    </svg>
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($fetchData['introductionDoctors'] as $newDocs)
                    <article role="banner" class="bg-white rounded-xl p-5 flex flex-col gap-5">
                        <div class="flex flex-col items-center gap-7">
                            <div class="relative">
                                <span
                                    class="absolute top-0 right-0 block w-4 h-4 bg-green border-[3px] border-white rounded-full"></span>
                                <div
                                    class="w-[60px] h-[60px] overflow-hidden rounded-full flex items-center justify-center">
                                    <img src="{{$newDocs->avatar}}" alt="doctor-image"
                                        class="w-full h-full object-cover" />
                                </div>
                            </div>
                            <div class="text-center space-y-1">
                                <h4 class="font-bold text-lg">دکتر {{$newDocs->full_name}}</h4>
                                <p class="text-secondary-400">{{ $newDocs->DocSpecialities() }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                    <use xlink:href="#sprite-location" />
                                </svg>
                                <span>{{$newDocs->DocProvinces()}}</span>
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
                            <p>{{ $newDocs->dr_waiting_time}}</p>
                        </div>
                        <a href="#" class="btn__blue--tint">دریافت نوبت</a>
                    </article>
                @endforeach

            </div>
        </div>
    </section>

</div>
