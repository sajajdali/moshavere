<div>
    <main class="py-16 bg-secondary-100">
        @if(! empty($err))
        <div class="container mb-4">
            <div class="error_badge">
                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="#sprite-warning" />
                </svg>
                <p>
                   {{$err}}
                </p>
            </div>
        </div>
        @endif
        <section class="container flex flex-col md:flex-row gap-10">
            <section class="basis-full md:basis-[60%] flex flex-col gap-6">
                <div class="bg-white p-5 rounded-lg">
                    <div class="flex flex-col gap-6">
                        <div class="flex flex-col items-center gap-4">
                            <p class="font-bold text-center">دریافت نوبت</p>
                            @if (setting(\Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_FOR_OTHERS_STATUS))
                                <div class="tabbar-radio__container">
                                    <label for="appointmentMyself">
                                        <input type="radio" id="appointmentMyself" name="appointmentTypeFor" checked
                                            value="mySelf" wire:model='form.app.for' />
                                        <div class="tabbar-radio__label">برای خودم</div>
                                    </label>

                                    <label for="appointmentOther">
                                        <input type="radio" id="appointmentOther" name="appointmentTypeFor"
                                            value="others" wire:model='form.app.for' />
                                        <div class="tabbar-radio__label">برای دیگری</div>
                                    </label>
                                </div>
                            @endif
                        </div>
                        <div class="appointment-myself__contain" wire:ignore.self>
                            <p class="font-bold">اطلاعات شخصی من</p>
                            <div class="space-y-3">
                                <div
                                    class="rounded-lg bg-secondary-200 flex items-center justify-between text-sm px-4 py-2">
                                    <p class="font-bold">نام و نام خانوادگی</p>
                                    <p>{{ $user->full_name }}</p>
                                </div>
                                <div
                                    class="rounded-lg bg-secondary-200 flex items-center justify-between text-sm px-4 py-2">
                                    <p class="font-bold">کد ملی</p>
                                    <p>{{ $user->national_cod }}</p>
                                </div>
                                <div
                                    class="rounded-lg bg-secondary-200 flex items-center justify-between text-sm px-4 py-2">
                                    <p class="font-bold">موبایل</p>
                                    <p>{{ $user->mobile }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="appointment-other__contain hide" wire:ignore.self>
                            <p class="font-bold">اطلاعات شخصی بیمار</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label for="first_name"
                                        class="after:content-['*'] after:mr-0.5 after:text-red block text-sm font-medium text-slate-700">نام</label>
                                    <input type="text" id="first_name" wire:model='form.otherApp.first_name'
                                        class="w-full border  @error('form.otherApp.first_name') border-red-500  border-rose-500 @else border-secondary-300 @enderror rounded-lg bg-primary-tint-100 py-2 px-3"
                                        placeholder="نام نمایشی شما" />
                                    @error('form.otherApp.first_name')
                                        <p class="text-sm text-red">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label for="last_name"
                                        class="after:content-['*'] after:mr-0.5 after:text-red block text-sm font-medium text-slate-700">نام
                                        خانوادگی</label>
                                    <input type="text" id="last_name" wire:model='form.otherApp.last_name'
                                        class="w-full border @error('form.otherApp.last_name')  border-rose-500 @else border-secondary-300 @enderror rounded-lg bg-primary-tint-100 py-2 px-3"
                                        placeholder="نام خانوادگی نمایشی شما" />
                                    <p class="text-[10px] text-red"></p>
                                    @error('form.otherApp.last_name')
                                        <p class="text-sm text-red">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label for="national_code"
                                            class="after:content-['*'] after:mr-0.5 after:text-red block text-sm font-medium text-slate-700">کد
                                            ملی</label>
                                        <label for="abroadUserSelect" class="flex items-center gap-2">
                                            <input type="checkbox" name="abroadUserSelect" id="abroadUserSelect"
                                                wire:model='form.otherApp.withOutNational_code' />
                                            <p class="text-sm">اتباع هستم</p>
                                        </label>
                                    </div>
                                    <input type="text" id="national_code" wire:model='form.otherApp.national_code'
                                        class="w-full border  @error('form.otherApp.national_code')   border-rose-500 @else border-secondary-300  @enderror rounded-lg bg-primary-tint-100 py-2 px-3"
                                        placeholder="کد ملی شما" />
                                    <p class="text-[10px] text-red"></p>
                                    @error('form.otherApp.national_code')
                                        <p class="text-sm text-red">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label for="phonenumber" class="block text-sm font-medium text-slate-700">شماره
                                            تلفن</label>
                                        <label for="patientPhone" class="flex items-center gap-2">
                                            <input type="checkbox" name="patientPhone" id="patientPhone"
                                                wire:model='form.otherApp.withOutMobile' />
                                            <p class="text-[10px]">بیمار موبایل ندارد</p>
                                        </label>
                                    </div>
                                    <input type="text" id="phonenumber" wire:model='form.otherApp.mobile'
                                        class="w-full border @error('form.otherApp.mobile')  border-rose-500 @else border-secondary-300 @enderror    rounded-lg bg-primary-tint-100 py-2 px-3"
                                        placeholder="شماره موبایل" />
                                    <p class="text-[10px] text-red"></p>
                                    @error('form.otherApp.mobile')
                                        <p class="text-sm text-red">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label for="countries"
                                        class="after:content-['*'] after:mr-0.5 after:text-red block text-sm font-medium text-slate-700">جنسیت</label>
                                    <select id="countries" wire:model='form.otherApp.gender'
                                        class="w-full border @error('form.otherApp.gender')
                                         border-rose-500 @else border-secondary-300 @enderror  rounded-lg bg-primary-tint-100 py-2 px-3">
                                        <option value="">انتخاب کنید...</option>
                                        <option value="man">مرد</option>
                                        <option value="woman">زن</option>
                                    </select>
                                    @error('form.otherApp.gender')
                                        <p class="text-sm text-red">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label for="phonenumber"
                                        class="block text-sm font-medium text-slate-700">بیمه</label>
                                    <input type="text" id="phonenumber" wire:model='form.otherApp.insurence'
                                        class="w-full border border-secondary-300 rounded-lg bg-primary-tint-100 py-2 px-3"
                                        placeholder="به فارسی" />
                                    @error('form.otherApp.insurence')
                                        <p class="text-sm text-red">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if (setting(Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_DESCRIPTION_IN_CHECKOUT_PAGE_REFUND))
                    <div class="bg-white rounded-lg p-4 space-y-4">
                        <div class="bg-white rounded-lg p-4  sm:grid-cols-2 gap-4">
                            <div class="border-2 border-secondary-100 p-4 space-y-4 rounded-lg">
                                <div class="space-y-2 flex">
                                    <div
                                        class="w-[40px] h-[40px] bg-primary-tint-100 rounded-full flex items-center justify-center text-primary-main">
                                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                            <use xlink:href="#sprite-accessibility" />
                                        </svg>
                                    </div>
                                    <p class="text-lg font-bold mr-2">توضیحات</p>
                                </div>
                                <p class="text-sm text-secondary-400">
                                    {!! nl2br(setting(Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_DESCRIPTION_IN_CHECKOUT_PAGE_REFUND)) !!}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </section>
            <section class="basis-full md:basis-[40%] flex flex-col gap-6">
                <div class="bg-white rounded-lg p-4 space-y-4">
                    <header>
                        <p class="font-bold">
                            اطلاعات نوبت
                        </p>
                    </header>
                    <main class="flex flex-col gap-4">
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
                        <div class="space-y-3 text-sm">
                            <div class="border border-secondary-200 rounded-lg p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-date" />
                                    </svg>
                                    <p>تاریخ نوبت</p>
                                </div>
                                <p>{{ verta($fetchData['date_for_blade'])->format('%d %B، %Y') }}</p>
                            </div>
                            <div class="border border-secondary-200 rounded-lg p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-time" />
                                    </svg>
                                    <p>زمان نوبت</p>
                                </div>
                                <p>{{ verta($fetchData['date_for_blade'])->format('H:i') }}</p>
                            </div>
                            @if ($fetchData['appSetting'][\Modules\AppointmentSetting\app\Models\AppointmentSetting::PAYMENT])
                                <div
                                    class="border border-secondary-200 rounded-lg p-4 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                            <use xlink:href="#sprite-credit" />
                                        </svg>
                                        <p>مبلغ ویزیت</p>
                                    </div>
                                    <p>30,000 ریال</p>
                                </div>
                            @endif
                            @if (isset($fetchData['places']->detial[\Modules\Place\app\Models\Place::DETAIL_ADDRESS]))
                                <div class="border border-secondary-200 rounded-lg p-4 flex items-center gap-2">
                                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-location" />
                                    </svg>
                                    <p class="w-[calc(100%-2.25rem)] leading-6">
                                        {{ $fetchData['places']->detial[\Modules\Place\app\Models\Place::DETAIL_ADDRESS] }}
                                    </p>
                                </div>
                            @endif
                        </div>
                        <button type="button" class="btn__blue--round-full" wire:click='setAppointment'>
                            <span wire:loading wire:target='setAppointment'>
                                <div role="status">
                                    <svg aria-hidden="true"
                                        class="w-5 h-5 text-gray-200 animate-spin dark:text-gray-600 fill-blue-700"
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
                            </span>
                            <span wire:loading.remove wire:target='setAppointment'>
                                تایید و ثبت نوبت</span>
                        </button>
                    </main>
                </div>
            </section>
        </section>
    </main>


    <div id="myModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden"
        aria-hidden="true">
        <!-- Modal content -->
        <div class="bg-white rounded-lg shadow-lg w-1/3 p-6">
            <div class="flex justify-between items-center">
                <h2 class="font-bold">شرایط و قوانین</h2>
                <button data-modal-close="myModal" class="text-gray-500 hover:text-gray-800">
                    &times;
                </button>
            </div>
            <div class="mt-4">
                @if (setting(Modules\Setting\Enum\SettingKeyEnum::PAYMENT_RULES_AND_CONDITION_DESCRIPTION))
                    <p>{!! nl2br(setting(Modules\Setting\Enum\SettingKeyEnum::PAYMENT_RULES_AND_CONDITION_DESCRIPTION)) !!}.</p>
                @endif
            </div>
            <div class="mt-4 flex justify-end">
                <button data-modal-close="myModal"
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    بستن
                </button>
            </div>
        </div>
    </div>

</div>
@push('scripts')
    <script>
        $(document).ready(function() {
            $('input[name="appointmentTypeFor"]').on('change', function() {
                if ($(this).val() === 'others') {
                    $('.appointment-other__contain').removeClass('hide');
                    $('.appointment-myself__contain').addClass('hide');
                } else {
                    $('.appointment-other__contain').addClass('hide');
                    $('.appointment-myself__contain').removeClass('hide');
                }
            });
        });
    </script>
    {{-- <script>
         @if (setting(Modules\Setting\Enum\SettingKeyEnum::PAYMENT_RULES_AND_CONDITION_STATUS))
                            <div class="flex items-center justify-between">
                                <label for="patientPhone" class="flex items-center gap-2">
                                    <input type="checkbox" name="patientPhone" id="patientPhone"
                                        wire:model='form.termAndCondition' />
                                    <p class="text-sm">با <a data-modal-toggle="myModal"
                                            class="text-blue-500 hover:text-blue-700 cursor-pointer">قوانین پرداخت</a>
                                        موافق
                                        هستم</p>
                                </label>
                            </div>
                        @endif
        document.addEventListener('DOMContentLoaded', function() {
            // Function to toggle modal visibility
            function toggleModal(modalId) {
                const modal = document.getElementById(modalId);
                modal.classList.toggle('hidden');
                modal.setAttribute('aria-hidden', modal.classList.contains('hidden'));
            }

            // Open modal
            document.querySelectorAll('[data-modal-toggle]').forEach(button => {
                button.addEventListener('click', function() {
                    const modalId = this.getAttribute('data-modal-toggle');
                    toggleModal(modalId);
                });
            });

            // Close modal
            document.querySelectorAll('[data-modal-close]').forEach(button => {
                button.addEventListener('click', function() {
                    const modalId = this.getAttribute('data-modal-close');
                    toggleModal(modalId);
                });
            });

            // Close modal when clicking outside the modal content
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', function(event) {
                    if (event.target === modal) {
                        toggleModal(modal.getAttribute('id'));
                    }
                });
            });
        });
    </script> --}}
@endpush
