<div>
    <!-- Spinner Overlay -->
    <div wire:loading>
        <div class="fixed inset-0 flex items-center justify-center bg-white bg-opacity-50 z-50 ">
            <div class="animate-spin rounded-full h-32 w-32 border-t-4 border-blue-500"></div>
        </div>
    </div>
    <main class="py-10 md:py-16 bg-secondary-100">
        <section class="container flex flex-col md:flex-row gap-10">
            <section class="basis-full md:basis-[60%] flex flex-col gap-6">
                <div class="bg-white rounded-lg space-y-4 p-4">
                    <header class="flex flex-col sm:flex-row items-center justify-between gap-3">
                        <p class="text-lg font-semibold">اطلاعات تخصصی پزشک</p>
                        @isset($doc->dr_rate)
                            <div class="bg-green text-white rounded-full py-2 px-5">
                                {{ $doc->dr_rate }}% رضایت مراجعین
                            </div>
                        @endisset
                    </header>
                    <main class="bg-secondary-100 rounded-lg p-4 flex flex-col sm:flex-row items-center gap-5">
                        <div
                            class="w-[70px] h-[70px] overflow-hidden rounded-full flex items-center justify-center border-2 border-white ring-2 ring-blue-sky">
                            <img src="{{ $doc->avatar }}" alt="doctor-image-name" />
                        </div>
                        <div
                            class="w-[calc(100%-70px-1.25rem)] flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="space-y-3 text-center sm:text-right">
                                <a class="text-lg font-bold"
                                    href="{{ route('front.doctor.profile', ['doctor_id' => $doc->id, 'doctor_name' => str_replace(' ', '_', $doc->full_name)]) }}">دکتر
                                    {{ $doc->full_name }}</a>
                                <p class="bg-secondary-200 rounded-lg py-2 px-3 text-sm">
                                    {{ $doc->DocSpecialities() }}
                                </p>
                            </div>
                            <div class="flex flex-col items-center sm:items-end gap-4">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-flag" />
                                    </svg>
                                    <p>{{ $doc->dr_display_experince }}</p>
                                </div>
                                <p class="text-secondary-400 text-sm">شماره نظام پزشکی: {{ $doc->dr_licence_number }}
                                </p>
                            </div>
                        </div>
                    </main>
                    <footer class="flex flex-col sm:flex-row items-center justify-between text-secondary-400 gap-4">
                        <div class="flex items-center gap-2">
                        </div>
                        <div class="flex items-center text-sm gap-4">
                            @if (isset($fetchData['isFavarite']))
                                <button wire:click='removeFromFavarite' type="button"
                                    class="flex items-center gap-2 text-lime-700">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-save" />
                                    </svg>
                                    <p>نشان شده</p>
                                </button>
                            @else
                                <button wire:click='addFavarite' type="button"
                                    class="flex items-center gap-2 hover:text-lime-700">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-save" />
                                    </svg>
                                    <p>نشان</p>
                                </button>
                            @endif

                            <button type="button" id="share" class="flex items-center gap-2 hover:text-blue-500">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                    <use xlink:href="#sprite-share" />
                                </svg>
                                <p>اشتراک گذاری</p>
                            </button>
                        </div>

                    </footer>

                    <button type="button" wire:click='reserveAppointment'
                            @if (!$fetchData['is_app_available']) disabled @endif style="width: 100%" class="mobile-only btn__blue--round-full-between">
                        @if ($doc->hasOnlineApp())
                            <p>دریافت نوبت حضوری دکتر {{ $doc->full_name }}</p>
                        @else
                            <p>دریافت نوبت دکتر {{ $doc->full_name }}</p>
                        @endif
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="#sprite-arrow-left-circle" />
                        </svg>
                    </button>
                </div>


                @if (isset($doc->dr_display_discription))
                    <div class="warning_badge">
                        <div class="py-4 flex items-center gap-3">
                            <svg class="w-5 h-5 text-secondary-300" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="#sprite-question" />
                            </svg>
                            <p class="w-[calc(100%-2rem)]">
                                {{ $doc->dr_display_discription }}
                            </p>
                        </div>
                    </div>
                @endif
                @isset($doc->dr_biography)
                    <div class="bg-white p-4 space-y-4 rounded-lg">
                        <header>
                            <h4 class="text-lg font-bold">درباره پزشک</h4>
                        </header>
                        <main class="bg-secondary-100 rounded-lg p-4 space-y-4">
                            <p>
                                {!! nl2br($doc->dr_biography) !!}
                            </p>
                            <div class="flex items-center justify-between gap-3">
                                <p class="bg-secondary-200 rounded-lg py-2 px-3 text-sm">
                                    {{ $doc->DocSpecialities() }}
                                </p>
                                <p class="text-secondary-400 text-sm">شماره نظام پزشکی: {{ $doc->dr_licence_number }}</p>
                            </div>
                        </main>
                        <footer class="flex flex-col sm:flex-row items-center justify-between text-secondary-400 gap-3">
                            <div class="flex items-center gap-2">

                            </div>
                        </footer>
                    </div>
                @endisset
                <!-- breadcrumb -->
                <div class="flex flex-wrap items-center gap-2 text-secondary-400">
                    <a href="{{ route('front.homePage') }}" class="hover:text-black">
                        {{ $fetchData['site_title'] }}</a>
                    <svg class="w-7 h-7" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="#sprite-chevron-left" />
                    </svg>
                    <span>{{ $doc->full_name }} دکتر</span>
                </div>
                <!-- end breadcrumb -->
                <!-- container -->
                <div class="flex flex-col gap-2">
                    @if (false)
                        {{-- feed back --}}
                        <div class="bg-white rounded-t-lg space-y-4 p-4">
                            <header class="flex flex-col sm:flex-row items-center justify-between gap-3">
                                <p class="text-lg font-semibold">اطلاعات تخصصی پزشک</p>
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-3 bg-green text-white rounded-full py-2 px-5">
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                            <use xlink:href="#sprite-heart" />
                                        </svg>
                                        <p>89% رضایت</p>
                                    </div>
                                    <p>از 200 نفر</p>
                                </div>
                            </header>
                            <main class="flex flex-col gap-3">
                                <div class="border-2 border-secondary-200 p-4 rounded-lg flex flex-col gap-3">
                                    <div class="flex items-center justify-between">
                                        <p>برخورد مناسب</p>
                                        <div class="flex items-center gap-2 font-bold">
                                            <p>5</p>
                                            <p class="text-xl">/</p>
                                            <p class="text-green">4.5</p>
                                        </div>
                                    </div>
                                    <progress dir="ltr" max="100" value="80"></progress>
                                </div>
                                <div class="border-2 border-secondary-200 p-4 rounded-lg flex flex-col gap-3">
                                    <div class="flex items-center justify-between">
                                        <p>توضیحات مفید پزشک</p>
                                        <div class="flex items-center gap-2 font-bold">
                                            <p>5</p>
                                            <p class="text-xl">/</p>
                                            <p class="text-green">4.5</p>
                                        </div>
                                    </div>
                                    <progress dir="ltr" max="100" value="80"></progress>
                                </div>
                                <div class="border-2 border-secondary-200 p-4 rounded-lg flex flex-col gap-3">
                                    <div class="flex items-center justify-between">
                                        <p>مهارت و تخصص</p>
                                        <div class="flex items-center gap-2 font-bold">
                                            <p>5</p>
                                            <p class="text-xl">/</p>
                                            <p class="text-green">4.5</p>
                                        </div>
                                    </div>
                                    <progress dir="ltr" max="100" value="80"></progress>
                                </div>
                            </main>
                            <footer
                                class="flex flex-col sm:flex-row items-center justify-between text-secondary-400 gap-4">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-eye" />
                                    </svg>
                                    <p>13 هزار بار مشاهده</p>
                                </div>
                                <div class="flex items-center text-sm gap-4">
                                    <button type="button" class="flex items-center gap-2">
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                            <use xlink:href="#sprite-save" />
                                        </svg>
                                        <p>نشان</p>
                                    </button>
                                    <button type="button" class="flex items-center gap-2">
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                            <use xlink:href="#sprite-share" />
                                        </svg>
                                        <p>اشتراک گذاری</p>
                                    </button>
                                </div>
                            </footer>
                        </div>
                    @endif

                    @if ($fetchData['comments']->isNotEmpty())
                        <div class="bg-white rounded-b-lg p-4 space-y-4">
                            <header>
                                <div class="flex items-center">
                                    <p class="">نظرات</p>
                                </div>
                            </header>
                            @for ($i = 0; $i < $fetchData['iteratorComments']; $i++)
                                <main class="flex flex-col gap-3">
                                    <div class="border-2 border-secondary-200 rounded-lg flex flex-col gap-4 p-4">
                                        <div class="flex flex-col sm:flex-row items-start justify-between gap-3">
                                            <div class="flex items-center gap-4">
                                                <div
                                                    class="w-[65px] h-[65px] rounded-full flex items-center justify-center bg-primary-main text-white font-bold text-2xl">
                                                    ن</div>
                                                <div class="w-[calc(100%-65px-0.75rem)] space-y-2">
                                                    <p>{{ $fetchData['comments'][$i]->user->full_name }}</p>

                                                </div>
                                            </div>
                                            <div
                                                class="flex items-center gap-2 bg-green/20 text-green rounded-full py-1 px-4 text-sm font-bold">
                                                <p class="w-[calc(100%-1.75rem)]">
                                                    {{ $fetchData['comments'][$i]->star }}</p>
                                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                                    <use xlink:href="#sprite-star-full" />
                                                </svg>
                                            </div>
                                        </div>
                                        <p class="leading-7">{{ $fetchData['comments'][$i]->body }}</p>
                                        @if (isset($fetchData['comments'][$i]->reply))
                                            <div class="text-center border-r-4 border-blue-500  mr-3 text-start">
                                                <p class="mr-2"> {{ $fetchData['comments'][$i]->reply }}</p>
                                            </div>
                                        @endif

                                    </div>
                                </main>
                            @endfor
                            @if (!isset($fetchData['iteratorStop']))
                                <button wire:click='loadMoreComment' type="button"
                                    class="w-full py-2 px-5 flex items-center justify-center gap-3">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-eye" />
                                    </svg>
                                    <p class="font-bold">نمایش بیشتر</p>
                                </button>
                            @endif
                        </div>
                    @endif
                    <div class="bg-white p-4 flex flex-col items-center justify-between gap-3">
                        @error('CommentSuccess')
                            <div class="container mb-4">
                                <div
                                    class="bg-blue-200 border border-2 border-bule-200  p-4 rounded-xl flex items-center gap-3">
                                    <p class="w-[calc(100%-3.25rem)] text-gray-600 leading-6 text-lg">
                                        {{ $message }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="w-full flex justify-between">
                                <p class="font-bold">نظرات خود را با دیگران به اشتراک بگذارید</p>
                                <button type="button" id="registerComment"
                                    class="btn__blue--round-full-outline font-semibold">
                                    ثبت نظر
                                </button>
                            </div>
                            <div class="border-2 border-secondary-200 rounded-lg flex flex-col gap-4 p-4 w-full"
                                id='registerCommentDiv' style="display: none" wire:ignore.self>
                                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mb-3">
                                    <div
                                        class="flex items-center gap-2 bg-green/20 text-green rounded-full py-1 px-4 text-sm font-bold">
                                        <div class="flex items-center gap-2">
                                            <input type="range" min="1" max="5" step="1"
                                                wire:model='form.comment.rate' value="5"
                                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                                            <span id="ratingValue" class="ml-2 text-gray-700 font-bold">5</span>
                                        </div>
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                            <use xlink:href="#sprite-star-full" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex flex-col md:flex-row gap-3 mt-3 mb-3">
                                    <form class="flex-grow relative flex items-center h-[40px]">
                                        <textarea wire:model='form.comment.body' type="text" rows="3"
                                            class="flex-grow w-full  border-2 border-secondary-200 rounded-lg px-3" placeholder="متن نظر را بنویسید"></textarea>
                                        @error('form.comment.body')
                                            <span class="text-rose-500">{{ $message }}</span>
                                        @enderror
                                    </form>
                                </div>
                                <div class="w-full flex justify-end">
                                    <button type="button" wire:click='addComment'
                                        class="btn__blue--round-full-outline font-semibold">
                                        ارسال
                                    </button>
                                </div>
                            </div>
                        @enderror
                    </div>
                </div>
                <!-- end container -->

            </section>
            <section class="basis-full md:basis-[40%] flex flex-col gap-6">
                <div class="bg-white rounded-lg p-4 space-y-4">
                    <header>
                        <p class="font-bold">
                            دریافت نوبت اینترنتی
                        </p>
                    </header>
                    <main class="flex flex-col gap-3">
                        <div class="text-sm border-2 border-secondary-200 p-3 rounded-lg space-y-3">
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                                <p class="font-bold">{{ $doc->dr_display_address }}</p>
                                <div class="flex gap-3">
                                    @isset($fetchData['tel'])
                                        <div class="bg-secondary-100 text-black rounded-lg py-2 px-5 text-sm">
                                            {{ $fetchData['tel'] }}
                                        </div>
                                    @endisset
                                    @isset($fetchData['navigate'])
                                        <a target="blank" href="{{ $fetchData['navigate'] }}"
                                            class="bg-secondary-100 text-black rounded-lg py-2 px-5 text-sm">
                                            مسیریابی
                                        </a>
                                    @endisset
                                </div>
                            </div>
                            @isset($fetchData['address'])
                                <div class="flex gap-3">
                                    <svg class="w-5 h-5 mt-[2px]" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="#sprite-location" />
                                    </svg>
                                    <p class="w-[calc(100%-2rem)]">{{ $fetchData['address'] }}
                                    </p>
                                </div>
                            @endisset
                        </div>
                        <button type="button" wire:click='reserveAppointment'
                            @if (!$fetchData['is_app_available']) disabled @endif class="btn__blue--round-full-between">
                            @if ($doc->hasOnlineApp())
                            <p>دریافت نوبت حضوری دکتر {{ $doc->full_name }}</p>
                            @else
                            <p>دریافت نوبت دکتر {{ $doc->full_name }}</p>
                            @endif
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="#sprite-arrow-left-circle" />
                            </svg>
                        </button>
                        @if ($doc->hasOnlineApp())
                            <button type="button" wire:click='reserveAppointment("online")'
                                @if (!$fetchData['is_app_available']) disabled @endif
                                class="btn__green--round-full-between">
                                <p>دریافت نوبت آنلاین (گفت و گو محور) {{ $doc->full_name }}</p>
                                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                    <use xlink:href="#sprite-arrow-left-circle" />
                                </svg>
                            </button>
                        @endif
                        @if (!$fetchData['is_app_available'])
                            <div class="error_badge">
                                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                    <use xlink:href="#sprite-warning" />
                                </svg>
                                <p>
                                    هم اکنون نوبت دهی برای این پزشک محدود شده است.
                                </p>
                            </div>
                        @endif
                    </main>
                </div>
                {{-- <div class="warning_badge">
                    <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="#sprite-warning" />
                    </svg>
                    <p>مراجعین محترم نوبت های ویزیت اسفند ماه پر شده است لطفا جهت دریافت نوبت
                        برای فروردین ماه از۲۷اسفند ساعت ۱۲شب به بعد به سایت مراجعه کنید</p>
                </div> --}}
                <div class="bg-white p-4 flex flex-col gap-4">
                    <p class="font-bold">
                        راه‌های ارتباطی با پزشک
                    </p>
                    <div class="border-2 border-secondary-200 rounded-lg flex flex-col p-4 gap-3">
                        <div class="flex items-center justify-between text-sm">
                            <p>
                                شماره تلفن: <span dir="ltr">{{ $doc->dr_display_mobile }}</span>
                            </p>
                            <a href="#" class="bg-secondary-100 rounded-lg py-2 px-4">
                                تماس
                            </a>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <p>
                                {{ $doc->dr_display_address }}
                            </p>
                            <a href="#" class="bg-secondary-100 rounded-lg py-2 px-4">
                                مسیریابی به مطب
                            </a>
                        </div>
                    </div>
                </div>
                @if (isset($fetchData['gallery']) && !empty($fetchData['gallery']))
                    <div class="bg-white p-4 flex flex-col gap-4 ">
                        @if (setting(\Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_GALLERY_TITLE))
                            <p class="font-bold">
                                {{ setting(\Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_GALLERY_TITLE) }}

                            </p>
                        @endif
                        @if (setting(\Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_GALLERY_BODY))
                            {{ setting(\Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_GALLERY_BODY) }}
                        @endif
                        <p>
                        </p>
                        <div
                            class="border-2 border-secondary-100 rounded-lg grid grid-cols-4 gap-12 p-4 overflow-auto  max-h-40">
                            @foreach ($fetchData['gallery'] as $gallery)
                                <a href="{{ $gallery }}" data-fancybox="gallery-a" data-caption="Gallery A #1">
                                    <img class="min-h-28 min-w-28 rounded-lg" src="{{ $gallery }}" />
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        </section>
    </main>
    @include('front::components.set-appointment.model')
</div>
@push('styles')
    <style>
        @media (min-width: 768px) {
            .mobile-only {
                display: none !important;
            }
        }
    </style>
@endpush
@push('scripts')
    <script>
        $(document).ready(function() {
            Livewire.on('lucnhModal', function() {
                $('.appointment__modal').addClass('opened');
            });
            $('body').on('click', '.dismissmodal', function() {
                $('.appointment__modal').removeClass('opened');
            });
            $('body').on('click', '.servicechoices', function() {
                @this.serviceHasSelected();
            });
            $(document).on('click', '#registerComment', function() {
                var $registerCommentDiv = $('#registerCommentDiv');

                if ($registerCommentDiv.css('display') === 'none') {
                    $registerCommentDiv.fadeIn();
                } else {
                    // Do something else if the div is already visible
                    $registerCommentDiv.fadeOut(); // For example, you could hide it
                }
            });
            const rangeInput = document.querySelector('input[type="range"]');
            const ratingValue = document.getElementById('ratingValue');
            rangeInput.addEventListener('input', function() {
                ratingValue.textContent = rangeInput.value;
            });
            $('#share').on('click', function() {
                var tempInput = document.createElement("input");
                tempInput.style.position = "absolute";
                tempInput.style.left = "-1000px";
                tempInput.value = window.location.href;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand("copy");
                document.body.removeChild(tempInput);
                alert('با موفقیت کپی شد!');
            });
            Livewire.on('swalError', function($obj) {
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: $obj.msg,
                    showConfirmButton: false,
                    timer: 2000
                });
            });
            let placeName = @json(isset($form['place_name']));
            let service = @json(isset($form['service']));

            if (placeName || service) {
                $('.appointment__modal').addClass('opened');
            }
        });
    </script>
@endpush
