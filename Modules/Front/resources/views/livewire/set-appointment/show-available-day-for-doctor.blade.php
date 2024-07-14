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
                        <img src="{{ $fetchData['doc']->avatar }}" alt="doctor-image-name" />
                    </div>
                    <div class="w-[calc(100%-70px-1.25rem)] space-y-3">
                        <p class="font-bold">دکتر {{ $fetchData['doc']->full_name }}</p>
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
            <div class="appointment__modal-left" wire:loading.class='opacity-75'>
                <div class="flex justify-between mb-4 align-center">
                    <p class="font-semibold">نوبت مورد نظر را انتخاب کنید</p>
                    <!-- Commented out code
                    <div>
                        {{-- @if (isset($fetchData['dont_show_first_available_day'])) --}}
                            <button class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                                wire:click='loadFirstApp'>
                                <svg wire:loading wire:target='loadFirstApp'
                                    class="animate-spin h-5 w-5 mr-3 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.372 0 0 5.372 0 12h4zm2 5.291A7.963 7.963 0 014 12H0c0 3.314 1.343 6.315 3.515 8.485l2.485-2.194z">
                                    </path>
                                </svg>
                                <span wire:loading.remove wire:target='loadFirstApp'>مشاهده اولین نوبت خالی</span>
                            </button>
                        {{-- @endif --}}
                        <button class="bg-blue-500 hover:bg-blue-700 font-thin text-white py-2 px-4 rounded"
                            wire:click='loadNextDays'>
                            <svg wire:loading wire:target='loadNextDays' class="animate-spin h-5 w-5 mr-3 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.372 0 0 5.372 0 12h4zm2 5.291A7.963 7.963 0 014 12H0c0 3.314 1.343 6.315 3.515 8.485l2.485-2.194z">
                                </path>
                            </svg>
                            <span wire:loading.remove wire:target='loadNextDays'>مشاهده روزهای بعدی</span>
                        </button>
                    </div> -->
                </div>
                <div class="select-appointment__container">
                    <div class="flex flex-col gap-3" wire:key='{{ uniqId() . '44' }}' wire:ignore.self>
                        @foreach ($fetchData['firstTreeAvailableAppointment'] as $date => $appointmentsWithDaysIndex)
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
                        <label for="appointment-{{ $date }}"
                            class="accordion__container accordion_appointment__container">
                            <div class="accordion_select__button">
                                <div class="accordion_select__text">
                                    <input type="radio" name="appointment"
                                        id="appointment-{{ $date }}" />
                                    @foreach ($appointmentsWithDaysIndex as $key => $value)
                                        @if ($value['status'] !== false)
                                            <p class="font-bold text-sm">
                                                {{ $value['day_name'] }}
                                                {{ $value['date_of_month'] }}
                                            </p>
                                        @break
                                    @endif
                                @endforeach
                            </div>
                            <div class="accordion_select__icon">
                                <svg class="w-7 h-7" xmlns="http://www.w3.org/2000/svg">
                                    <use xlink:href="#sprite-chevron-left-circle" />
                                </svg>
                            </div>
                        </div>
                        <div class="accordion_select__content">
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
                                                        id="time-{{ $uniqueId }}" wire:model='form.time'
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
        </div>
        <button type="button" class="btn__blue--round-full mt-4" id="nextstep_btn"
            wire:click='TimeForReservesation'>
            <span>مرحله بعد</span>
        </button>
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
