<div>
    <!-- search input -->
    <header class="bg-white py-3 border-t border-secondary-200">
        @error('query')
            <span class="bg-rose-200 py-2 px-5 ms-3 w-full rounded-sm">
                {{ $message }}
            </span>
        @enderror
        <section class="px-4 w-full max-w-[770px] mx-auto">
            <form wire:submit='RenewSearch'
                class="flex items-center p-3 w-full relative bg-white rounded-3xl border-4 border-blue-sky/50">
                <input type="text" class="text-sm md:text-base flex-grow border-none outline-none pr-7 md:pr-12"
                    wire:model='query' placeholder="جستجوی پزشک، کلینیک یا تخصص..." />
                <svg class="absolute top-1/2 -translate-y-1/2 right-3 md:right-5 text-gray-700 w-5 h-5 md:w-6 md:h-6"
                    xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="#sprite-search" />
                </svg>
                <button type="submit" class="btn__blue">
                    <div role="status" wire:loading wire:target='RenewSearch'>
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
                    <span class="text-sm md:text-base" wire:loading.remove wire:target='RenewSearch'>جست و جو</span>
                </button>
            </form>
        </section>
    </header>

    <!-- main section -->
    <main class="bg-secondary-100 py-10">
        <div wire:loading>
            <div class="fixed inset-0 flex items-center justify-center bg-white bg-opacity-50 z-50 ">
                <div class="animate-spin rounded-full h-32 w-32 border-t-4 border-blue-500"></div>
            </div>
        </div>
        @if (isset($fetchData['set_appointment_message']))
            <div class="container mb-4">
                <div class="bg-blue-200  p-4 rounded-xl flex items-center gap-3">
                    <p class="w-[calc(100%-3.25rem)] leading-6 text-lg">
                        {{ $fetchData['set_appointment_message'] }}
                    </p>
                </div>
            </div>
        @endif
        <section class="container flex flex-col items-stretch md:flex-row md:items-start gap-6">
            <aside class="w-full basis-full md:w-[40%] md:basis-[40%] flex flex-col gap-4">
                @if (!empty($filter))
                    <div class="bg-white rounded-lg p-4 space-y-4">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-bold">فیلتر انتخاب شده</p>
                            <button class="text-red" wire:click="removeFilter('all')">حذف فیلتر</button>
                        </div>
                        @foreach ($filter as $item)
                            <div class="flex flex-wrap gap-3">
                                <span class="badge__tag">
                                    <span>{{ $item }}</span>
                                    <button type="button" wire:click="removeFilter('{{ $item }}')">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg">
                                            <use xlink:href="#sprite-x" />
                                        </svg>
                                    </button>
                                </span>
                            </div>
                        @endforeach

                    </div>
                @endif
                <div class="bg-white rounded-lg p-4 space-y-4">
                    <div class="accordion__container--2" id="specialityFilter" wire:ignore.self>
                        <div class="accordion__button text-primary-main">
                            <p class="font-bold">جست و جو در تخصص ها</p>
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="#sprite-chevron-down-circle" />
                            </svg>
                        </div>
                        <div class="accordion__content">
                            <ul class="filter-list__container">
                                @foreach ($fetchData['specilities'] as $key => $specility)
                                    <li class="filter-list__item applyFilter specialityfilterActive"
                                        data-index="speciality" data-name="{{ $specility->title }}" wire:ignore.self>
                                        {{ $specility->title }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg p-4 space-y-4">
                    <div class="accordion__container--2" id="serviceFilter" wire:ignore.self>
                        <div class="accordion__button text-primary-main">
                            <p class="font-bold">جست و جو در بخش ها</p>
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="#sprite-chevron-down-circle" />
                            </svg>
                        </div>
                        <div class="accordion__content">
                            <ul class="filter-list__container">
                                @foreach ($fetchData['services'] as $key => $service)
                                    <li class="filter-list__item applyFilter serviceFilterActive" data-index="service"
                                        data-name="{{ $service->title }}">
                                        {{ $service->title }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </aside>
            <article class="w-full basis-full md:w-[60%] md:basis-[60%] flex flex-col gap-6">
                <div class="flex flex-col gap-4">
                    @if (empty($fetchData['reuslt']))
                        <div class="container mb-4">
                            <div class="bg-rose-100 border border-rose-200 text-gray-600  p-4 rounded-xl flex items-center gap-3">
                                <p class="w-[calc(100%-3.25rem)] leading-6 text-lg">
                                 نتیجه ای یافت نشد..
                                </p>
                            </div>
                        </div>
                    @endif
                    @foreach ($fetchData['reuslt'] as $forPart => $collection)
                        @if ($forPart == 'place' && !empty($collection))
                            @foreach ($collection as $place)
                                <div class="bg-white flex flex-col gap-5 rounded-lg p-4">
                                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                                        <div
                                            class="w-[calc(100%-70px-1.25rem)] flex flex-col sm:flex-row items-center justify-between gap-3">
                                            <div class="space-y-3 text-center md:text-right">
                                                <p class="text-lg font-bold">{{ $place->title }}</p>
                                                <p class="bg-secondary-200 rounded-lg py-2 px-3 text-sm">
                                                    {{ $place->user?->count() ?? 0 }} پزشک
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border border-secondary-200 p-3 rounded-lg space-y-3">
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <p class="font-bold">{{ $place->title }}</p>
                                            <div class="flex gap-3">
                                                @if (isset($place->detail[Modules\Place\app\Models\Place::DETAIL_KEY_NUMBERS]))
                                                    <span
                                                        class="font-bold bg-secondary-100 text-black rounded-lg py-2 px-5 text-sm">
                                                        {{ implode(',', $place->detail[Modules\Place\app\Models\Place::DETAIL_KEY_NUMBERS]) }}
                                                    </span>
                                                @endif
                                                @if (isset($place->detail[Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION]))
                                                    <a href="https://maps.google.com/maps?daddr={{ $place->detail[Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION][Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION_LAT] }},{{ $place->detail[Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION][Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION_LNG] }}"
                                                        class="font-bold bg-secondary-100 hover:bg-secondary-200 text-black rounded-lg py-2 px-5 text-sm"
                                                        target="blank">
                                                        مسیریابی
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                        @if (isset($place->detail[Modules\Place\app\Models\Place::DETAIL_ADDRESS]))
                                            <div class="flex gap-3">
                                                <svg class="w-5 h-5 mt-[2px]" xmlns="http://www.w3.org/2000/svg">
                                                    <use xlink:href="#sprite-location" />
                                                </svg>
                                                <p class="w-[calc(100%-2rem)]">
                                                    {{ $place->detail[Modules\Place\app\Models\Place::DETAIL_ADDRESS] }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex justify-end">
                                        <a href="#" wire:click='placeSelected("{{ $place->id }}")'
                                            class="btn__blue--round-full-between">
                                            <span class="font-semibold">دریافت نوبت در این مطب</span>
                                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                                <use xlink:href="#sprite-chevron-left-circle" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        @if ($forPart == 'doctors' && !empty($collection))
                            @foreach ($collection as $docIndex => $doctor)
                                <div class="bg-white flex flex-col gap-5 rounded-lg p-4">
                                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                                        <div
                                            class="w-[70px] h-[70px] overflow-hidden rounded-full flex items-center justify-center border-2 border-white ring-2 ring-blue-sky">
                                            <img src="{{ $doctor->avatar }}" alt="doctor-image-name" />
                                        </div>
                                        <div
                                            class="w-[calc(100%-70px-1.25rem)] flex flex-col sm:flex-row items-center justify-between gap-3">
                                            <div class="space-y-3 text-center md:text-right">
                                                <p class="text-lg font-bold">دکتر {{ $doctor->full_name }}</p>
                                                <p class="bg-secondary-200 rounded-lg py-2 px-3 text-sm">
                                                    {{ $doctor->DocSpecialities() }}
                                                </p>
                                            </div>
                                            <div class="flex flex-col items-end gap-4">
                                                {{-- <div
                                                    class="flex items-center gap-3 bg-green/10 text-green rounded-full py-2 px-5">
                                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                                        <use xlink:href="#sprite-heart-outline" />
                                                    </svg>
                                                    <p> پرشک محبوب </p>
                                                </div> --}}
                                                <p class="text-secondary-400 text-sm">شماره نظام پزشکی:
                                                    {{ $doctor->dr_licence_number }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-end">
                                        <a href="#" wire:click='getApp("{{ $doctor->id }}")'
                                            class="btn__blue--round-full-between">
                                            <span class="font-semibold">دریافت نوبت دکتر
                                                {{ $doctor->full_name }}</span>
                                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                                <use xlink:href="#sprite-chevron-left-circle" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        @if ($forPart == 'service' && !empty($collection))
                            @foreach ($collection as $service)
                                <div class="bg-white flex flex-col gap-5 rounded-lg p-4">
                                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                                        @if (isset($service->icon))
                                            <div
                                                class="w-[70px] h-[70px] overflow-hidden rounded-full flex items-center justify-center border-2 border-white ring-2 ring-blue-sky">
                                                <img src="{{ $service->icon }}" alt="doctor-image-name" />
                                            </div>
                                        @endif
                                        <div
                                            class="w-[calc(100%-70px-1.25rem)] flex flex-col sm:flex-row items-center justify-between gap-3">
                                            <div class="space-y-3 text-center md:text-right">
                                                <p class="text-lg font-bold">{{ $service->title }}</p>
                                                <p class="bg-secondary-200 rounded-lg py-2 px-3 text-sm">
                                                    {{ $service->user->count() }} پزشک
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-end">
                                        <a href="#" wire:click='getAppFromService({{ $service->id }})'
                                            class="btn__blue--round-full-between">
                                            <span class="font-semibold">دریافت نوبت از بخش</span>
                                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                                <use xlink:href="#sprite-chevron-left-circle" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endforeach
                </div>
                @if (!isset($fetchData['wholeContentLoaded']))
                    <div class="flex  justify-center w-full">
                        <button id="loadMoreContent"
                            class='bg-sky-200 hover:bg-sky-400 rounded-xl py-2 px-6 max-w-fit	'
                            wire:click='loadMoreResult'>
                            <div role="status" wire:loading wire:target='loadMoreResult'>
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
                            <span wire:loading.remove wire:target='loadMoreResult'>
                                بارگزاری بیشتر نتایج...
                            </span>
                            </butto>
                    </div>
                @endif
            </article>
        </section>
    </main>
</div>
@push('scripts')
    <script>
        $(document).ready(function() {
            $(document).on('click', '.accordion__button', function() {
                var $button = $(this);
                var $item = $button.closest('.accordion__container--2');

                // Toggle the 'open' class for the clicked accordion
                $item.toggleClass('open');

                // Close all nested accordions if this one is closed
                if (!$item.hasClass('open')) {
                    $item.find('.accordion__container--2.open').removeClass('open');
                }
            });

            $(document).on('click', '.applyFilter', function() {
                var name = $(this).data('name');
                var category = $(this).data('index');
                if (category == 'speciality') {
                    $('.specialityfilterActive').removeClass('active');
                } else {
                    $('.serviceFilterActive').removeClass('active');
                }
                $(this).addClass('active');
                @this.applyFilter(name, category);
            });
            Livewire.on('removeFilter', function($removedItem) {
                if ($removedItem == 'speciality') {
                    $('.specialityfilterActive').removeClass('active');
                    $('#specialityFilter').removeClass('open');
                } else {
                    $('.serviceFilterActive').removeClass('active');
                    $('#serviceFilter').removeClass('open');
                }

            });
            Livewire.on('removeFilterAll', function() {
                $('.specialityfilterActive').removeClass('active');
                $('.serviceFilterActive').removeClass('active');
                $('.accordion__container--2').removeClass('open');
            });
            $(window).scroll(function() {
                // Calculate the necessary variables
                var scrollTop = $(this).scrollTop();
                var windowHeight = $(this).height();
                var documentHeight = $(document).height();

                // Calculate the distance from the bottom of the page
                var bottomDistance = documentHeight - (scrollTop + windowHeight);

                // Define a threshold, e.g., when the user is 200px away from the bottom
                var loadMoreThreshold = 200;

                // Check if the user has scrolled to the bottom
                if (bottomDistance <= loadMoreThreshold) {
                    loadMoreContent(); // Load more content
                }
            });
            var $is_loaded = true;

            function loadMoreContent() {
                if ($is_loaded) {
                    $is_loaded = false;
                    $('#loadMoreContent').click();
                    setTimeout(() => {
                        $is_loaded = true;
                    }, 4500);
                }
            }

        });
    </script>
@endpush
