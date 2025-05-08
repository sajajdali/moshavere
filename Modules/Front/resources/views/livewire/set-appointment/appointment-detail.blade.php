<div>
    <!-- Spinner Overlay -->
    <div wire:loading>
        <div class="fixed inset-0 flex items-center justify-center bg-white bg-opacity-50 z-50 ">
            <div class="animate-spin rounded-full h-32 w-32 border-t-4 border-blue-500"></div>
        </div>
    </div>
    <main class="py-6 bg-secondary-100">
        @if (session()->has('success') || isset($fetchData['success']))
            <div class="bg-emerald-200 text-gray-500 text-lg max-w-3xl text-center py-3 px-5 rounded-lg mb-5 mx-auto">
                @if (session()->has('success'))
                    {{ session()->get('success') }}
                @endif
                @isset($fetchData['success'])
                    {{ $fetchData['success'] }}
                @endisset
            </div>
        @endif
        @if (isset($fetchData['alert']) || session()->has('error'))
            <div class="bg-red text-white text-lg max-w-3xl text-center py-3 px-5 rounded-lg mb-5 mx-auto">
                @if (session()->has('error'))
                    {{ session()->get('error') }}
                @endif
                @isset($fetchData['alert'])
                    {{ $fetchData['alert'] }}
                @endisset
            </div>
        @endif

        <div
            class="@if ($fetchData['stauts']['enum'] == Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_CANCEL) bg-rose-100
       @else bg-white @endif  rounded-2xl p-4 max-w-3xl mx-auto space-y-4">
            @if ($fetchData['monitoring'])
                <div class="border-2 border-indigo-500 bg-indigo-100  p-4 rounded-xl flex items-center gap-3 ">
                    <svg class="w-6 h-6 text-red w-10 h-10 " xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="#sprite-warning" />
                    </svg>
                    <p class="text-base">
                        نوبت شما در <strong>انتظار تایید</strong> است و بعد از تایید ، وضعیت نوبت از <strong>طریق
                            پیامک</strong> به شما اطلاع رسانی میشود!
                    </p>
                </div>
            @endif
            @if ($fetchData['stauts']['payment'])
                <section>
                    <div class="flex flex-col gap-2 sm:flex-row items-center justify-between bg-red/10 py-3.5 px-4 rounded-2xl mb-4"
                        style="word-spacing: 0.08rem;">
                        <img src="{{ front_asset('assets/svg/warning-icon.svg') }}" />
                        <p class="font-bold text-sm px-4 text-gray-700 ">
                            <span>
                                نوبت شما با موفقیت <span class="text-red">رزرو شد</span>.
                                برای تایید نوبت باید مبلغ {{ number_format($fetchData['stauts']['price']) }} ریال را به
                                صورت
                                آنلاین پرداخت کنید تا نوبت شما ثبت شود و در صورت عدم
                                پرداخت نوبت شما حذف خواهد شد.
                            </span>
                            @if (setting(\Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_DETAIL_PAYMENT_DESCRIPTION_STATUS) != null &&
                                    setting(\Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_DETAIL_PAYMENT_DESCRIPTION_TEXT) != null &&
                                    !$fetchData['app']->isOnline())
                                <span>
                                    {{ setting(\Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_DETAIL_PAYMENT_DESCRIPTION_TEXT) }}
                                </span>
                            @endif
                        </p>
                        <button type="button"
                            class="font-bold text-red confirm_swal_alert text-sm cancelApp min-w-fit">لغو
                            نوبت</button>
                    </div>
                    <p class="font-bold my-3">
                        <a href="#" id="discountBtn" class="text-primary-main  mr-2">کد تخفیف دارید؟</a>
                    </p>
                    <div class="my-3" id="collapsible-content" style="display: none" wire:ignore.self>
                        <div
                            class="grid gap-4 md:grid-cols-5 items-start border-2 border-solid  border-secondary-100 rounded-2xl py-3.5 px-4  @if (isset($fetchData['status']['price_after_discount'])) opacity-40 @endif ">
                            <input type="text" id="discount-code" wire:model="form.discount_code"
                                @if (isset($fetchData['status']['price_after_discount'])) disabled @endif
                                placeholder="کد تخفیف خود را وارد کنید"
                                class="col-span-4 w-full px-4 py-2 border @error('form.discount_code') border-rose-500  @else  border-gray-300 @enderror rounded-lg focus:outline-none focus:border-success-500">
                            <button wire:click='discount' wire:target='discount' wire:loading.attr='disabled'
                                @if (isset($fetchData['status']['price_after_discount'])) disabled @endif
                                class="col-span-1   @if (isset($fetchData['status']['price_after_discount'])) bg-lime-500 @else bg-blue-500 @endif hover:bg-blue-700 text-white font-bold py-2 px-6 px-4 rounded text-center">
                                @if (isset($fetchData['status']['price_after_discount']))
                                    <span>
                                        تایید شد
                                    </span>
                                @else
                                    <span wire:loading.remove wire:target='discount'>اعمال کد</span>
                                    <div role="status" wire:loading wire:target='discount'>
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
                                @endif
                            </button>
                        </div>
                        @error('form.discount_code')
                            <p class="text-rose-500	 mr-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div
                        class="flex flex-col gap-3 md:flex-row items-center justify-between border-2 border-solid border-secondary-100 rounded-2xl py-3.5 px-4">
                        @if (isset($fetchData['status']['price_after_discount']))
                            <div>
                                <p class="font-bold">
                                    جهت فعالسازی نوبت، مبلغ <s
                                        class="text-rose-500">{{ number_format($fetchData['stauts']['price']) }}</s>
                                    {{ number_format($fetchData['status']['price_after_discount']) }} ریال پرداخت
                                    نمایید
                                    @if (isset($fetchData['payment']['termAndCondition']))
                                        <div class="flex items-center mt-3" wire:ignore>
                                            <input id="termAndConditionAggrement" type="checkbox"
                                                class="form-checkbox h-3 w-3 text-blue-600" checked>
                                            <label for="termAndConditionAggrement" class="text-sm text-gray-500 mr-2">
                                                با
                                                <button id="termAndConditionModalLunch"
                                                    class="text-blue-400 hover:text-blue-700">شرایط و قوانین </button>
                                                پرداخت موافق هستم.</label>
                                        </div>
                                    @endif
                                </p>
                            </div>
                        @else
                            <div>
                                <p class="font-bold">
                                    جهت فعالسازی نوبت، مبلغ {{ number_format($fetchData['stauts']['price']) }} ریال
                                    پرداخت
                                    نمایید
                                </p>
                                @if (isset($fetchData['payment']['termAndCondition']))
                                    <div class="flex items-center mt-3" wire:ignore>
                                        <input id="termAndConditionAggrement" type="checkbox"
                                            class="form-checkbox h-3 w-3 text-blue-600" checked>
                                        <label for="termAndConditionAggrement" class="text-sm text-gray-500 mr-2"> با
                                            <button id="termAndConditionModalLunch"
                                                class="text-blue-400 hover:text-blue-700">شرایط و قوانین </button>
                                            پرداخت موافق هستم.</label>
                                    </div>
                                @endif
                            </div>
                        @endif
                        <button type="button" wire:click='GotoPayment' id="paymentBtn"
                            class=" flex items-center justify-center
                             gap-3 py-2 px-4 md:px-5 bg-primary-main border-2 border-primary-main
                             text-white rounded-full
                             hover:bg-primary-tint-300 transition-colors focus:ring-2 ring-blue-sky !w-fit !px-3 ">
                            <p>پرداخت و فعال سازی</p>
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="#sprite-arrow-left-circle" />
                            </svg>
                        </button>
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
            @if ($fetchData['app'])
                <section>
                    <h3 class="text-sm font-bold mb-4 flex justify-between">
                        اطلاعات نوبت شما
                        @if ($this->fetchData['stauts']['enum'] == Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_CANCEL)
                            <span
                                class="inline-flex items-center rounded-md bg-rose-400 px-2 py-1 text-xs font-semibold text-white ring-1 ring-inset ring-gray-500/10 mr-2">کنسل
                                شده</span>
                        @endif
                        @isset($fetchData['returnToApp'])
                            <a target="blank" href="{{ $fetchData['returnToApp'] }}"
                                class="bg-rose-500 hover:bg-rose-700 text-white font-bold py-2 px-4 rounded-full">
                                <span>بازگشت به اپلیکیشن</span>
                            </a>
                        @endisset
                        @if (!disableUi() && $fetchData['app']->isOnline() && $fetchData['app']->isAppActive())
                            <a href="{{ route('front.user.chatroom', ['onlineAppId' => $fetchData['app']->online->first()->id]) }}"
                                class="bg-emerald-500 hover:bg-lime-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300 ease-in-out">
                                ورود به چت
                            </a>
                        @endif
                    </h3>
                    <div class="border-card space-y-4">
                        <div class="flex flex-col md:flex-row items-stretch md:items-start justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <div class="relative bg-primary-main/50 p-0.5 w-14 h-14 rounded-full shrink-0">
                                    <span class="block w-4 h-4 bg-white p-0.5 absolute right-0 top-0">
                                        <span class="block w-full h-full rounded-full bg-green"></span>
                                    </span>
                                    <img class="w-full h-full object-cover rounded-full"
                                        src="{{ $fetchData['app']->doctor->avatar }}" alt="doctor" />
                                </div>

                                <div class="text-sm space-y-2">
                                    <p class="font-bold">دکتر {{ $fetchData['app']->doctor->full_name }}</p>
                                    <p class="bg-secondary-200 px-3 py-1 rounded-md">
                                        {{ $fetchData['app']->doctor->DocSpecialities() }}
                                    </p>
                                </div>
                            </div>
                            @if (!$fetchData['app']->kind->isOnline())
                                <div
                                    class="flex flex-row md:flex-col gap-2 justify-between md:justify-start items-center md:items-end">
                                    <a href="#" class="font-bold text-sm bg-secondary-100 px-3 py-1 rounded-2xl">
                                        تماس با مطب
                                    </a>
                                    @if (isset($fetchData['app']->doctor->dr_licence_number))
                                        <p class="text-xs">شماره نظام
                                            پزشکی:{{ $fetchData['app']->doctor->dr_licence_number }}
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>
                        @if (!$fetchData['app']->kind->isOnline())
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
                                    <iframe class="w-full mb-4 rounded-2xl" src="{{ $fetchData['mapUrl'] }}"
                                        width="400" height="300" style="border: 0" allowfullscreen=""
                                        loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

                                    <a target="blank" href="{{ $fetchData['navigation'] }}"
                                        class="btn__blue--round-full-between !w-full !font-bold !text-base my-3">
                                        <span>مسیریابی</span>
                                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                            <use xlink:href="#sprite-arrow-left-circle" />
                                        </svg>
                                    </a>
                                @endisset
                            </div>
                        @endif
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
                            @if (!$fetchData['app']->kind->isOnline())
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
                                        <strong>زمان نوبت:</strong>
                                    </p>
                                    @if ($fetchData['stauts']['enum'] != Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_CANCEL)
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
                            @else
                                <div class="visit-detail flex mt-3">
                                    <p>
                                        <object class="inline-block"
                                            data="{{ front_asset('assets/svg/calendar.svg') }}"></object>
                                        <strong>تاریخ دریافت نوبت:</strong>
                                    </p>
                                    <p class="mr-3">{{ verta($fetchData['app']->created_at)->format('d F') }}</p>
                                </div>
                                <div class="visit-detail flex mt-3">
                                    <p>
                                        <object class="inline-block"
                                            data="{{ front_asset('assets/svg/calendar.svg') }}"></object>
                                        <strong>زمان نوبت:</strong>
                                    </p>
                                    <p class="mr-3">{{ verta($fetchData['app']->date_visit)->format('d F') }}</p>
                                </div>
                            @endif
                            @if ($fetchData['stauts']['payment'])
                                <div class="visit-detail flex mt-3">
                                    <p>
                                        <object class="inline-block"
                                            data="{{ front_asset('assets/svg/solar_card-outline.svg') }}"></object>
                                        <strong>مبلغ ویزیت:</strong>
                                    </p>
                                    <p class="mr-2">{{ number_format($fetchData['stauts']['price']) }} ریال</p>
                                </div>
                            @endif

                            @if (isset($fetchData['app']->place->detail[Modules\Place\app\Models\Place::DETAIL_ADDRESS]) &&
                                    !$fetchData['app']->kind->isOnline())
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
                            @if ($fetchData['cancel'])
                                <div class="w-full flex justify-end">
                                    <button
                                        class=" cancelApp flex items-center justify-between gap-3 py-2 px-4 bg-red border-2  text-white rounded-full hover:bg-primary-tint-300 transition-colors">
                                        <span>کنسل کردن نوبت</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                        @if ($fetchData['stauts']['payment'])
                            <div class="h-0.5 w-full bg-secondary-100"></div>

                            <div class="text-sm space-y-2">
                                <p class="font-bold text-base !mb-4">جزئیات پرداخت</p>

                                <div class="border-card flex justify-between">
                                    <p>مبلغ قابل پرداخت</p>
                                    @if (isset($fetchData['status']['price_after_discount']))
                                        <s class="text-rose-500">
                                            <p>{{ number_format($fetchData['stauts']['price']) }} ریال</p>
                                        </s>
                                    @else
                                        <p>{{ number_format($fetchData['stauts']['price']) }} ریال</p>
                                    @endif
                                </div>
                                @if (isset($fetchData['status']['price_after_discount']))
                                    <div class="border-card flex justify-between">
                                        <p>
                                            <span>مبلغ بعد از تخفیف</span>
                                        </p>
                                        <p>{{ number_format($fetchData['status']['price_after_discount']) }} ریال</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </section>
            @endif

        </div>
    </main>
    <input type="hidden" value="{{ $fetchData['authCheck'] }}" id="swalStatus">

    @if (isset($fetchData['payment']['termAndCondition']))
        {{-- term and condition modal --}}
        <section class="appointment__modal max-h-min" wire:ignore.self>
            <header class="appointment__modal-header  ">
                <button type="button"
                    class="bg-white border-2 border-red text-red flex items-center py-3 px-5 rounded-xl gap-3 dismissmodal">
                    <span>بستن</span>
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="#sprite-x" />
                    </svg>
                </button>
            </header>
            <main class="appointment__modal-container p-10 flex flex-column justify-between ">
                <div class="space-y-3">
                    <p>
                        {!! nl2br($fetchData['payment']['termAndCondition']) !!}
                    </p>
                </div>
            </main>
        </section>
    @endif
</div>
@push('scripts')
    <script>
        $(document).ready(function() {
            $('body').on('click', '#discountBtn', function() {
                var content = $('#collapsible-content');
                if (content.css('display') === 'none') {
                    content.fadeIn();
                } else {
                    content.fadeOut();
                }
            });
            $('body').on('click', '#termAndConditionModalLunch', function() {
                $('.appointment__modal').addClass('opened');
            });
            $('body').on('click', '.dismissmodal', function() {
                $('.appointment__modal').removeClass('opened');
            });
            let SAMessage = @json($fetchData['sweetAlert']['msg'] ?? false);
            let SAIcon = @json($fetchData['sweetAlert']['icon'] ?? false);
            var status = $('#swalStatus').val();
            if(SAMessage){
                Swal.fire({
                    title: 'توجه!',
                    text: SAMessage,
                    icon: SAIcon,
                    showCancelButton: false,
                    confirmButtonText: 'متوجه شدم',
                    confirmButtonColor: '#008000', // You can change the color to your preference
                });
            }
            $('body').on('click', '.cancelApp', function() {
                var status = $('#swalStatus').val();
                if (status) {
                    Swal.fire({
                        title: 'توجه!',
                        text: 'از کنسل کردن نوبت مطمعن هستید؟',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'بله کنسل شود',
                        cancelButtonText: 'خیر',
                        cancelButtonColor: '#1e90ff',
                        confirmButtonColor: '#d33', // You can change the color to your preference
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.cancelAppontment();
                        }
                    });
                } else {
                    @this.authNeeded();
                }
            });
            $('body').on('click', '#termAndConditionAggrement', function() {
                if ($(this).is(':checked')) {
                    $('#paymentBtn').prop('disabled', false);
                    $('#paymentBtn').removeClass('bg-gray-300');
                    $('#paymentBtn').addClass('bg-primary-main');
                    $('#paymentBtn').addClass('border-primary-main');
                } else {
                    $('#paymentBtn').prop('disabled', true);
                    $('#paymentBtn').removeClass('bg-primary-main');
                    $('#paymentBtn').removeClass('border-primary-main');
                    $('#paymentBtn').addClass('bg-gray-300');
                }
            });
        });
    </script>
@endpush
