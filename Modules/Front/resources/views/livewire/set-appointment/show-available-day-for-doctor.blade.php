<div>
    <div wire:loading>
        <div class="fixed inset-0 flex items-center justify-center bg-white bg-opacity-50 z-50 ">
            <div class="animate-spin rounded-full h-32 w-32 border-t-4 border-blue-500"></div>
        </div>
    </div>
    <main class="py-16 bg-secondary-100">
        <div class="list_of_available_day_container">
            <div class="appointment__modal-right">
                <div class="bg-secondary-100 rounded-lg p-4 flex items-center gap-5 mb-4">
                    <div
                        class="w-[70px] h-[70px] overflow-hidden rounded-full flex items-center justify-center border-2 border-white ring-2 ring-blue-sky">
                        <img src="{{ $fetchData['doc']->getUserAvatar() }}" alt="doctor-image-name" />
                    </div>
                    <div class="w-[calc(100%-70px-1.25rem)] space-y-3">
                        <a href="{{ route('front.doctor.profile', ['doctor_id' => $fetchData['doc']->id, 'doctor_name' => str_replace(' ', '_', $fetchData['doc']->full_name)]) }}"
                            class="font-bold">{{$fetchData['doc']->speciality_type == 1 ? 'دکتر' : ''}} {{ $fetchData['doc']->full_name }}</a>
                        <p class="bg-secondary-200 rounded-lg py-2 px-3 text-sm">
                            {{ $fetchData['doc']->DocSpecialities() }}
                        </p>
                    </div>
                </div>
                <div class="space-y-3 border border-secondary-200 rounded-lg p-4">
                    <p class="font-bold">{{ $fetchData['places']->title }}</p>
                    <div class="flex items-center gap-2">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="#sprite-location" />
                        </svg>
                        <p class="w-[calc(100%-2.25rem)] leading-6 text-sm">
                            {{ isset($fetchData['places']->detail[\Modules\Place\app\Models\Place::DETAIL_ADDRESS]) ? $fetchData['places']->detail[\Modules\Place\app\Models\Place::DETAIL_ADDRESS] : '' }}
                        </p>
                    </div>
                </div>
            </div>
            @if (isset($fetchData['firstTreeAvailableAppointment']) &&
                    !empty($fetchData['firstTreeAvailableAppointment']) &&
                    $fetchData['isAppointmentActive'])
                <div class="appointment__modal-left" wire:loading.class='opacity-75'>
                    <div class="flex justify-between mb-4 align-center">
                        <p class="font-semibold">نوبت مورد نظر را انتخاب کنید</p>
                    </div>
                    <div class="select-appointment__container">
                        <div class="flex flex-col gap-3" wire:key='{{ uniqId() . '44' }}' wire:ignore.self>
                            @foreach ($fetchData['firstTreeAvailableAppointment'] as $date => $appointmentsWithDaysIndex)
                                @php
                                    $allFalse = collect($appointmentsWithDaysIndex)->every(
                                        fn($item) => $item['status'] === false,
                                    );
                                @endphp
                                @if (!$allFalse)
                                    @once
                                        @foreach ($appointmentsWithDaysIndex as $eachTime => $appointmentDetail)
                                            @if ($appointmentDetail['status'] == false)
                                                @continue
                                            @endif
                                            <label for="appointment-{{ $eachTime }}"
                                                class="border-2 accordion_appointment__container border-secondary-200 rounded-lg flex items-center gap-4 py-3 px-4">
                                                <input type="radio" class="scroll_down" name="appointment"
                                                    id="appointment-{{ $eachTime }}" wire:model='form.time'
                                                    value="{{ $appointmentDetail['time_stamp'] . ',' . $appointmentDetail['until'] }}" />
                                                <div class="w-[calc(100%-2rem)] space-y-2 text-sm">
                                                    <p>نزدیک‌ترین نوبت خالی</p>
                                                    <p class="font-bold">{{ $appointmentDetail['date_of_month'] }} - ساعت
                                                        {{ $appointmentDetail['from'] }}</p>
                                                </div>
                                            </label>
                                        @break
                                        @endforeach
                                    @endonce
                                @endif
                            <label for="appointment-{{ $date }}"
                                class="accordion__container accordion_appointment__container @if ($allFalse) bg-rose-200 remove_open @endif">
                                <div class="accordion_select__button">
                                    <div class="accordion_select__text">
                                        @if ($allFalse)
                                            <p class="font-bold text-sm">
                                                {{ verta($date)->format('%d %b %Y') }} - پر شده
                                            </p>
                                        @else
                                            @foreach ($appointmentsWithDaysIndex as $key => $value)
                                                @if ($value['status'] !== false)
                                                    <p class="font-bold text-sm">
                                                        {{ $value['day_name'] }}
                                                        {{ $value['date_of_month'] }}
                                                    </p>
                                                @break
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                                <div class="accordion_select__icon">
                                    <svg class="w-7 h-7" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-chevron-left-circle" />
                                    </svg>
                                </div>
                            </div>
                            <div class="accordion_select__content" style="overflow-y: scroll">
                                <div class="tabbar__container">
                                    <div class="tabbar__container-main">
                                        <div class="tabbar__container-content grid-4x active">
                                            @foreach ($appointmentsWithDaysIndex as $index => $eachTimeAppointment)
                                                @if ($eachTimeAppointment['status'] == false)
                                                    <label class="opacity-30 line-through">
                                                        <input type="radio" class="hidden sr-only" />
                                                        <p
                                                            class="flex items-center justify-center py-2 px-4 border-2 border-transparent rounded-full text-secondary-400 bg-rose-300 cursor-not-allowed font-bold text-center">
                                                            {{ verta($eachTimeAppointment['from'])->format('H:i') }}
                                                        </p>
                                                    </label>
                                                @else
                                                    @once
                                                        <!-- skip the first time -->
                                                        @if ($eachTimeAppointment['status'] != false && $loop->first)
                                                            @continue
                                                        @endif
                                                    @endonce
                                                    @php
                                                        $uniqueId = $index . '-' . microtime(true) . '-' . $index;
                                                    @endphp
                                                    <label for="time-{{ $uniqueId }}"
                                                        class="select-time__radio cursor-pointer">
                                                        <input type="radio" class="hidden sr-only scroll_down"
                                                            wire:loading.attr='disabled'
                                                            id="time-{{ $uniqueId }}"
                                                            wire:model='form.time'
                                                            value="{{ $eachTimeAppointment['time_stamp'] . ',' . $eachTimeAppointment['until'] }}" />
                                                        <p>{{ $eachTimeAppointment['from'] }}</p>
                                                    </label>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
                @if (isset($msg) && $msg != false)
                    <div class="relative bg-sky-200 border border-sky-200 text-gray-600 px-4 py-3 rounded-lg mt-3"
                        role="alert">
                        <strong class="font-bold">نکته!</strong>
                        <span class="block sm:inline">{{ $msg }}.</span>
                    </div>
                @else
                    <button type="button" wire:click='loadMoreDays'
                        class="w-full py-2 hover:text-blue-500 px-5 flex items-center justify-center gap-3 mt-5">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="#sprite-eye" />
                        </svg>
                        <p class="font-bold">نمایش بیشتر</p>
                    </button>
                @endif
            @elseif($fetchData['isAppointmentActive'] == false)
                <div class="relative bg-sky-200 border border-sky-200 text-gray-600 px-4 py-3 rounded-lg mt-3"
                    role="alert">
                    <strong class="font-bold">نکته!</strong>
                    <span class="block sm:inline">نوبت دهی پزشک انتخابی محدود شده است!</span>
                </div>
            @else
                <div class="relative bg-sky-200 border border-sky-200 text-gray-600 px-4 py-3 rounded-lg mt-3"
                    role="alert">
                    <strong class="font-bold">نکته!</strong>
                    <span class="block sm:inline">تمامی نوبت های مربوط به این پزشک پر میباشد لطفا در یک روز دیگر
                        امتحان
                        کنید!</span>
                </div>
            @endif
</div>
        @if (isset($fetchData['firstTreeAvailableAppointment']) &&
                !empty($fetchData['firstTreeAvailableAppointment']) &&
                $fetchData['isAppointmentActive']
        )
            <button type="button" class="btn__blue--round-full mt-4" id="nextstep_btn"
                wire:click='TimeForReservesation'>
                <span>مرحله بعد</span>
            </button>
        @endif
</div>
</main>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('body').on('click', '.accordion_appointment__container', function() {
            // Remove 'open' class from all accordion containers
            $('.accordion_appointment__container').removeClass('open');
            // Add 'open' class to the clicked accordion container
            $(this).addClass('open');
        });

        $('body').on('change', '.scroll_down', function() {
            // Check if the radio input is checked
            if ($(this).is(':checked')) {
                var nextStepButton = $('#nextstep_btn');
                // Check if the next step button element exists
                if (nextStepButton.length) {
                    @this.TimeForReservesation();
                }
            }
        });
        Livewire.on('scrollToBottom', function() {
            $('html, body').animate({
                scrollTop: $(document).height() - 150
            }, 1000);
            Swal.fire({
                position: "center",
                icon: "success",
                title: "روز های جدید اضافه شدند",
                showConfirmButton: false,
                timer: 1500
            });
        })
    });
</script>
@endpush
