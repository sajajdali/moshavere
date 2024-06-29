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
                        <a data-description="میخواهید نوبت به بین مریض تبدیل شود؟" data-title="تغییر وضعیت "
                            data-confirmbtn="بله تغییر کند" data-action="changeType" data-id="aw"
                            class=" font-bold text-red confirm_swal_alert text-sm" data-label="نوبت" href="">لغو نوبت</a>
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
                            <p class="font-bold">
                                جهت فعالسازی نوبت، مبلغ <s
                                    class="text-rose-500">{{ number_format($fetchData['stauts']['price']) }}</s>
                                {{ number_format($fetchData['status']['price_after_discount']) }} تومان پرداخت
                                نمایید
                            </p>
                        @else
                            <p class="font-bold">
                                جهت فعالسازی نوبت، مبلغ {{ number_format($fetchData['stauts']['price']) }} تومان پرداخت
                                نمایید
                            </p>
                        @endif
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
            @if ($fetchData['app'])
            <section>
                <h3 class="text-sm font-bold mb-4">اطلاعات نوبت شما</h3>

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
                                <strong>زمان نوبت:</strong>
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
                            <div class="visit-detail flex mt-3">
                                <p>
                                    <object class="inline-block"
                                        data="{{ front_asset('assets/svg/solar_card-outline.svg') }}"></object>
                                    <strong>مبلغ ویزیت:</strong>
                                </p>
                                <p class="mr-2">{{ number_format($fetchData['stauts']['price']) }} تومان</p>
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
                            <p class="font-bold text-base !mb-4">جزئیات پرداخت</p>

                            <div class="border-card flex justify-between">
                                <p>مبلغ قابل پرداخت</p>
                                @if (isset($fetchData['status']['price_after_discount']))
                                    <s class="text-rose-500">
                                        <p>{{ number_format($fetchData['stauts']['price']) }} تومان</p>
                                    </s>
                                @else
                                    <p>{{ number_format($fetchData['stauts']['price']) }} تومان</p>
                                @endif
                            </div>
                            @if (isset($fetchData['status']['price_after_discount']))
                                <div class="border-card flex justify-between">
                                    <p>
                                        <span>مبلغ بعد از تخفیف</span>
                                    </p>
                                    <p>{{ number_format($fetchData['status']['price_after_discount']) }} تومان</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </section>
            @endif

        </div>
    </main>
</div>
@push('scripts')
    <script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/admin.sweetalert.js') }}"></script>
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
        });
    </script>
@endpush
