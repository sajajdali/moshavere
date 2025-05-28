<section class="dashboard__main" id="userAppointmentSection" wire:ignore.self>
    @if (isset($form['appointments']) && $form['appointments']->isNotEmpty())
        <div class="bg-white p-4 space-y-5 rounded-lg">
            <h3 class="font-bold cursor-pointer">
                لیست نوبت‌های شما
            </h3>
            @foreach ($form['appointments'] as $key => $appointmentUser)
                <div x-data="{ open: false }"
                    class="relative bg-white p-4 space-y-5 rounded-lg border border-secondary-100">
                    <div
                        class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-cyan-500 border- border-white rounded-full -top-2 -right-2 dark:border-gray-900">
                        {{ $key + 1 }}</div>
                    <div class=" flex justify-between  cursor-pointer px-4" @click="open = !open">
                        <div>
                            <span>بخش: </span>
                            <span> {{ $appointmentUser->service->title }}</span>
                        </div>
                        <div>
                            <span> تاریخ نوبت: </span>
                            <span>
                                {{ verta($appointmentUser->date_visit)->format('Y/m/d ساعت H:i') }}
                            </span>
                        </div>
                        <div>
                            وضعیت:
                            <span
                                class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">
                                {{ $appointmentUser->status->getName() }}
                            </span>
                        </div>
                    </div>
                    <!-- Collapsible content -->
                    <div x-show="open" x-collapse
                        class="flex flex-col gap-5 border border-secondary-200 rounded-lg p-4">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div
                                class="w-[70px] h-[70px] overflow-hidden rounded-full flex items-center justify-center border-2 border-white ring-2 ring-blue-sky">
                                <img src="{{$appointmentUser->doctor->getUserAvatar()}}" alt="doctor-image-name" />
                            </div>
                            <div
                                class="w-[calc(100%-70px-1.25rem)] flex flex-col sm:flex-row items-center justify-between gap-3">
                                <div class="space-y-3 text-center md:text-right">
                                    <p class="text-lg font-bold">{{$appointmentUser->doctor->speciality_type == 1 ? 'دکتر' : ''}} {{ $appointmentUser->doctor->full_name }}</p>
                                    <p class="bg-secondary-200 rounded-lg py-2 px-3 text-sm text-center">
                                        {{ $appointmentUser->doctor->DocSpecialities() }}
                                    </p>
                                </div>
                                <div class="flex flex-col items-end gap-4">
                                    @if($appointmentUser->isOnline() && $appointmentUser->online->isNotEmpty())
                                    <a href="{{route('front.user.chatroom',['onlineAppId' => $appointmentUser->online->first()?->id ])}}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300 ease-in-out">
                                        ورود به چت
                                    </a>
                                    @endif
                                    <p class="text-secondary-400 text-sm">شماره نظام پزشکی:
                                        {{ $appointmentUser->doctor->dr_licence_number }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="border border-secondary-200 p-3 rounded-lg space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <p class="font-bold">{{ $appointmentUser->doctor->dr_display_address }}</p>
                                <div class="flex gap-3">
                                    <a href="tel:{{ $appointmentUser->doctor->dr_display_mobile }}"
                                        class="font-bold bg-secondary-100 text-black rounded-lg py-2 px-5 text-sm">تماس</a>
                                    <a href="{{ $appointmentUser->doctor->drDisplayNavigation }}"
                                        class="font-bold bg-secondary-100 text-black rounded-lg py-2 px-5 text-sm">مسیریابی</a>
                                </div>
                            </div>
                            @isset($appointmentUser->place->detail[Modules\Place\app\Models\Place::DETAIL_ADDRESS])
                                <div class="flex gap-3">
                                    <svg class="w-5 h-5 mt-[2px]" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-location" />
                                    </svg>
                                    <p class="w-[calc(100%-2rem)]">
                                        {{ $appointmentUser->place->detail[Modules\Place\app\Models\Place::DETAIL_ADDRESS] }}
                                    </p>
                                </div>
                            @endisset
                        </div>
                        <hr class="border-secondary-200" />
                        <div class="space-y-3 text-sm">
                            <p class="font-bold">اطلاعات نوبت</p>
                            <div class="border border-secondary-200 rounded-lg p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-date" />
                                    </svg>
                                    <p>تاریخ نوبت</p>
                                </div>
                                <p>{{ verta($appointmentUser->visit_date)->format(' %d %b  Y') }}</p>
                            </div>
                            <div class="border border-secondary-200 rounded-lg p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-time" />
                                    </svg>
                                    <p>زمان نوبت</p>
                                </div>
                                <p>{{ verta($appointmentUser->visit_date)->format('H:i') }}</p>
                            </div>
                            @if ($appointmentUser->details[Modules\AppointmentUser\app\Models\AppointmentUser::DETAIL_PAYMENT]['status'])
                                <div
                                    class="border border-secondary-200 rounded-lg p-4 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                            <use xlink:href="#sprite-credit" />
                                        </svg>
                                        <p>مبلغ پرداختی</p>
                                    </div>
                                    @if (isset($appointmentUser->detail[Modules\AppointmentUser\app\Models\AppointmentUser::DETAIL_PAYMENT]))

                                    <p>{{number_format($appointmentUser->detail[Modules\AppointmentUser\app\Models\AppointmentUser::DETAIL_PAYMENT][Modules\AppointmentUser\app\Models\AppointmentUser::DETAIL_PAYMENT_PRICE])}} تومان</p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if ($appointmentUser->details[Modules\AppointmentUser\app\Models\AppointmentUser::DETAIL_PAYMENT]['status'])

                        <hr class="border-secondary-200" />
                        <div class="space-y-3 text-sm">
                            <p class="font-bold text-lg">جزیات پرداخت</p>
                            <main class="flex flex-col gap-3">
                                <div
                                    class="border border-secondary-200 rounded-lg p-4 flex items-center justify-between">
                                    <p>مبلغ نوبت رزرو</p>
                                    @if (isset($appointmentUser->detail[Modules\AppointmentUser\app\Models\AppointmentUser::DETAIL_PAYMENT]))
                                    <p><span class="font-bold">{{number_format($appointmentUser->detail[Modules\AppointmentUser\app\Models\AppointmentUser::DETAIL_PAYMENT][Modules\AppointmentUser\app\Models\AppointmentUser::DETAIL_PAYMENT_PRICE])}}</span> تومان</p>
                                    @endif
                                </div>
                            </main>
                        </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
            {{-- TODO::addAlert --}}
            {{-- نوبتی یافت نشد! --}}
    @endif

</section>
