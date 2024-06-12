<div>
    <main class="py-16 bg-secondary-100">
        <main class="appointment__modal-container">
            <div class="appointment__modal-right">
                <div class="bg-secondary-100 rounded-lg p-4 flex items-center gap-5">
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
            <div class="appointment__modal-left">
                <div class="flex justify-between">
                    <p class="font-semibold">نوبت مورد نظر را انتخاب کنید</p>
                    <button class="bg-blue-500 hover:bg-blue-700 text-white py-2 px-4 rounded" wire:click='loadNextDays'>
                        <svg wire:loading wire:target='loadNextDays' class="animate-spin h-5 w-5 mr-3 text-white" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.372 0 0 5.372 0 12h4zm2 5.291A7.963 7.963 0 014 12H0c0 3.314 1.343 6.315 3.515 8.485l2.485-2.194z">
                        </path>
                    </svg>
                        <span wire:loading.remove wire:target='loadNextDays'>مشاهده روزهای بعدی</span>
                      </button>

                </div>
                <div class="select-appointment__container">
                    <div class="flex flex-col gap-3">
                        @foreach ($fetchData['firstTreeAvailableAppointment'] as $date => $appointmentsWithDaysIndex)
                            @once
                                @foreach ($appointmentsWithDaysIndex as $eachTime => $appointmentDetail)
                                    <label for="appointment-{{ $eachTime }}"
                                        class="border-2 accordion_appointment__container  border-secondary-200 rounded-lg flex items-center gap-4 py-3 px-4">
                                        <input type="radio" class="scroll_down" name="appointment"
                                            id="appointment-{{ $eachTime }}" wire:model='form.time'
                                            value="{{ $appointmentDetail['time_stamp'] }}" />
                                        <div class="w-[calc(100%-2rem)] space-y-2 text-sm">
                                            <p>نزدیک‌ترین نوبت خالی</p>
                                            <p class="font-bold"> {{ $appointmentDetail['date_of_month'] }} - ساعت
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
                                    <p class="font-bold text-sm">{{ $appointmentsWithDaysIndex[0]['day_name'] }}
                                        {{ $appointmentsWithDaysIndex[0]['date_of_month'] }}</p>
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
                                                @once
                                                    {{-- skip the first time --}}
                                                    @if ($loop->first)
                                                        @continue
                                                    @endif
                                                @endonce
                                                <label for="time-{{ $index + 548752 }}" class="select-time__radio">
                                                    <input type="radio" class="hidden sr-only scroll_down"
                                                        id="time-{{ $index + 548752 }}" wire:model='form.time'
                                                        value="{{ $eachTimeAppointment['time_stamp'] }}" />
                                                    <p>{{ $eachTimeAppointment['from'] }}</p>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
            @error('*')
                <h3>{{ $message }}</h3>
            @enderror
            <button type="button" class="btn__blue--round-full" id="nextstep_btn"
                wire:click='TimeForReservesation'>
                <svg wire:loading  wire:target='TimeForReservesation' class="animate-spin h-5 w-5 mr-3 text-white" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.372 0 0 5.372 0 12h4zm2 5.291A7.963 7.963 0 014 12H0c0 3.314 1.343 6.315 3.515 8.485l2.485-2.194z">
                    </path>
                </svg>
                <span wire:loading.remove wire:target='TimeForReservesation' >مرحله بعد</span>
            </button>
        </div>
    </main>
</main>

</div>
@push('scripts')
<script>
    $(document).ready(function() {
        $('.accordion_appointment__container').on('click', function() {
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
                    // Scroll to the next step button
                    $('html, body').animate({
                        scrollTop: nextStepButton.offset().top - 200
                    }, 1500); // Adjust the duration as needed
                } else {
                    console.error("Next step button not found.");
                }
            }
        });
    });
</script>
@endpush
