<div>
    <main class="py-6 bg-secondary-100">
        <div class="bg-white rounded-2xl p-4 max-w-3xl mx-auto space-y-4">
            @if ($fetchData['stauts']['payment'])
                <section>
                    <div
                        class="flex flex-col gap-2 sm:flex-row items-center justify-between bg-red/10 py-3.5 px-4 rounded-2xl mb-4">
                        <img src="{{ front_asset('assets/svg/warning-icon.svg') }}" />
                        <p class="font-bold text-sm">
                            <span>نوبت شما در حالت </span><span class="text-red">منتظر پرداخت</span><span> است.</span>
                        </p>
                        <button class="font-bold text-red text-sm">لغو نوبت</button>
                    </div>
                    <div
                        class="flex flex-col gap-3 md:flex-row items-center justify-between border-2 border-solid border-secondary-100 rounded-2xl py-3.5 px-4">
                        <p class="font-bold">
                            جهت فعالسازی نوبت، مبلغ {{ number_format($fetchData['stauts']['price']) }} تومان پرداخت
                            نمایید
                        </p>

                        <a href="#" class="btn__blue--round-full !w-fit !px-3">
                            <p>پرداخت و فعال سازی</p>
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="#sprite-arrow-left-circle" />
                            </svg>
                        </a>
                    </div>
                </section>
            @endif
            @if ($fetchData['description'])
                <section>
                    <div class="p-4 bg-yellow/20 border-2 border-yellow border-solid rounded-2xl">
                        <div class="flex items-center gap-2 mb-3">
                            <object data="{{ front_asset('assets/svg/warning-icon-lg-yellow.svg') }}"></object>
                            <h3 class="font-bold">توضیحات مربوط به نوبت</h3>
                        </div>
                        <ul class="flex flex-col gap-2 items-stretch pr-2 text-sm">
                            {!! nl2br($fetchData['description']) !!}
                        </ul>
                    </div>
                </section>
            @endif
            <section>
                <h3 class="text-sm font-bold mb-4">اطلاعات نوبت شما</h3>

                <div class="border-card space-y-4">
                    <div class="flex flex-col md:flex-row items-stretch md:items-start justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="relative bg-primary-main/50 p-0.5 w-14 h-14 rounded-full shrink-0">
                                <span class="block w-4 h-4 bg-white p-0.5 absolute right-0 top-0">
                                    <span class="block w-full h-full rounded-full bg-green"></span>
                                </span>
                                <img class="w-full h-full object-cover rounded-full" src="../assets/images/doctor/1.png"
                                    alt="doctor" />
                            </div>

                            <div class="text-sm space-y-2">
                                <p class="font-bold">دکتر {{ $fetchData['app']->doctor->full_name }}</p>
                                <p class="bg-secondary-200 px-3 py-1 rounded-md">
                                    {{ $fetchData['app']->doctor->DocSpecialities() }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex flex-row md:flex-col gap-2 justify-between md:justify-start items-center md:items-end">
                            <a href="#" class="font-bold text-sm bg-secondary-100 px-3 py-1 rounded-2xl">
                                تماس با مطب
                            </a>
                            @if (isset($fetchData['app']->doctor->dr_licence_number))
                                <p class="text-xs">شماره نظام پزشکی:{{ $fetchData['app']->doctor->dr_licence_number }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="border-card">
                        <p class="font-bold text-sm mb-2">{{ $fetchData['app']->place->title }}</p>
                        @if (isset($fetchData['app']->place->detail[Modules\Place\app\Models\Place::DETAIL_ADDRESS]))
                            <p class="text-sm mb-4">
                                <object class="inline-block mb-0.5"
                                    data="{{ front_asset('assets/svg/location-icon.svg') }}"></object>
                                <span>
                                    {{ $fetchData['app']->place->detail[Modules\Place\app\Models\Place::DETAIL_ADDRESS] }}
                                </span>
                            </p>
                        @endif
                        @isset($fetchData['mapUrl'])
                            <iframe class="w-full mb-4 rounded-2xl" src="{{ $fetchData['mapUrl'] }}" width="400"
                                height="300" style="border: 0" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>

                            <a target="blank" href="{{ $fetchData['navigation'] }}"
                                class="btn__blue--round-full-between !w-full !font-bold !text-base my-3">
                                <span>مسیریابی</span>
                                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                    <use xlink:href="#sprite-arrow-left-circle" />
                                </svg>
                            </a>
                        @endisset
                    </div>

                    <div class="border-card bg-secondary-100 p-5">
                        <p class="text-sm font-bold mb-3">نوع نوبت: {{ $fetchData['app']->kind->getName() }}</p>
                        <div class="visit-detail flex">
                            <p>
                                <object class="inline-block"
                                    data="{{ front_asset('assets/svg/fluent-patient.svg') }}"></object>
                                <strong>نام و نام خانوادگی مراجعه کننده:</strong>
                            </p>
                            <p class="mr-3">{{ $fetchData['app']->user->full_name }}</p>
                        </div>

                        <div class="visit-detail flex mt-3">
                            <p>
                                <object class="inline-block"
                                    data="{{ front_asset('assets/svg/calendar.svg') }}"></object>
                                <strong>تاریخ نوبت:</strong>
                            </p>
                            <p class="mr-3">{{ verta($fetchData['app']->date_visit)->format('d F') }}</p>
                        </div>

                        <div class="visit-detail flex mt-3">
                            <p>
                                <object class="inline-block"
                                    data="{{ front_asset('assets/svg/timeclock.svg') }}"></object>
                                <strong>زمان نوبت</strong>
                            </p>
                            @if ($this->fetchData['stauts']['enum'] != Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_CANCEL)
                                <span class="mr-3">
                                    @if (isset($fetchData['app']->date_visit))
                                        {{ verta($fetchData['app']->date_visit)->format('H:i') }}
                                    @else
                                        ---
                                    @endif
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">کنسل
                                    شده</span>
                            @endif
                        </div>
                        @if ($fetchData['stauts']['payment'])
                            <div class="visit-detail mt-3">
                                <p>
                                    <object class="inline-block"
                                        data="{{ front_asset('assets/svg/solar_card-outline.svg') }}"></object>
                                    <span>مبلغ ویزیت</span>
                                </p>
                                <p>{{ number_format($fetchData['stauts']['price']) }} تومان</p>
                            </div>
                        @endif

                            @if (isset($fetchData['app']->place->detail[Modules\Place\app\Models\Place::DETAIL_ADDRESS]))
                                <div class="visit-detail flex mt-3 !mb-0">
                                    <p>
                                        <object class="inline-block"
                                            data="{{ front_asset('assets/svg/location-icon.svg') }} "></object>
                                        <span>
                                            {{ $fetchData['app']->place->detail[Modules\Place\app\Models\Place::DETAIL_ADDRESS] }}
                                        </span>
                                    </p>
                                    <p></p>
                                </div>
                            @endif
                    </div>
                    @if ($fetchData['stauts']['payment'])
                    <div class="h-0.5 w-full bg-secondary-100"></div>

                    <div class="text-sm space-y-2">
                        <p class="font-bold text-base !mb-4">جزیات پرداخت</p>

                        <div class="border-card flex justify-between">
                            <p>مبلغ نوبت رزرو</p>
                            <p>15,000 تومان</p>
                        </div>
                        <div class="border-card flex justify-between">
                            <p>
                                <span>تخفیف</span>
                                <a href="#" class="text-primary-main">کد تخفیف دارید؟</a>
                            </p>
                            <p>0 تومان</p>
                        </div>
                    </div>
                    @endif
                </div>
            </section>
        </div>
    </main>
</div>
