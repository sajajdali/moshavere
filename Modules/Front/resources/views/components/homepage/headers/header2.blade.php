<div>
    @php
        $enumNameSpace = \Modules\Setting\Enum\SettingKeyEnum::class;
        $SettingMn = \Modules\Setting\Entities\Setting::class;
        $collection = \Modules\Setting\Entities\Setting::whereIn('setting_key', [
            $enumNameSpace::HEADER1_TITLE1,
            $enumNameSpace::HEADER1_TITLE2,
            $enumNameSpace::HEADER1_SHOW_BUTTON1,
            $enumNameSpace::HEADER1_SHOW_BUTTON2,
            $enumNameSpace::HEADER1_BUTTON_TITLE1,
            $enumNameSpace::HEADER1_BUTTON_TITLE2,
            $enumNameSpace::HEADER1_BUTTON_HREF1,
            $enumNameSpace::HEADER1_BUTTON_HREF2,
            $enumNameSpace::HEADER1_IMAGE,
            $enumNameSpace::HEADER1_TITLE_COLOR,
            $enumNameSpace::HEADER1_TITLE_NOT_SHOW_MOBILE,
            $enumNameSpace::HEADER1_TITLE_NOT_SHOW_DESKTOP,
        ])->get();
        $headerTextColor = 'text-' . $SettingMn::vc($collection, $enumNameSpace::HEADER1_TITLE_COLOR) ?? 'white';
        $hideHeaderTitlesMobile = $SettingMn::vcBool($collection, $enumNameSpace::HEADER1_TITLE_NOT_SHOW_MOBILE);
        $hideHeaderTitlesDesktop = $SettingMn::vcBool($collection, $enumNameSpace::HEADER1_TITLE_NOT_SHOW_DESKTOP);
        $headerTitleVisibilityClass = '';

        if ($hideHeaderTitlesMobile && $hideHeaderTitlesDesktop) {
            $headerTitleVisibilityClass = null;
        } elseif ($hideHeaderTitlesMobile) {
            $headerTitleVisibilityClass = 'hidden md:block';
        } elseif ($hideHeaderTitlesDesktop) {
            $headerTitleVisibilityClass = 'md:hidden';
        }
    @endphp
    <!-- header -->
    <header class="relative bg-primary-main pt-1 space-y-10 md:space-y-16">
        <!-- Background Pattern (Blue with SVG pattern) -->
        <div class="absolute inset-0 bg-cover bg-repeat min-h-[300px]"
             style="background-image: url({{ assetStorage($SettingMn::vc($collection, $enumNameSpace::HEADER1_IMAGE)) }});"></div>
        <section class="relative w-full container mx-auto px-5 flex flex-col md:flex-row items-center gap-6 md:gap-8">
            <!-- Right Section: Texts -->
            <div class="md:w-1/2 w-full text-right">
                @if ($headerTitleVisibilityClass !== null && $SettingMn::vc($collection, $enumNameSpace::HEADER1_TITLE1))
                    <h1 class="{{ $headerTitleVisibilityClass }} text-2xl md:text-3xl {{ $headerTextColor }} font-bold mb-4">
                        {{ $SettingMn::vc($collection, $enumNameSpace::HEADER1_TITLE1) }}
                    </h1>
                @endif

                @if ($headerTitleVisibilityClass !== null && $SettingMn::vc($collection, $enumNameSpace::HEADER1_TITLE2))
                    <p class="{{ $headerTitleVisibilityClass }} {{ $headerTextColor }} text-sm md:text-base mb-6">
                        {{ $SettingMn::vc($collection, $enumNameSpace::HEADER1_TITLE2) }}
                    </p>
                @endif

                @if (
                    $SettingMn::vc($collection, $enumNameSpace::HEADER1_SHOW_BUTTON1) ||
                        $SettingMn::vc($collection, $enumNameSpace::HEADER1_SHOW_BUTTON2))
                    <div
                        class="w-full max-w-[450px] mx-auto bg-white p-6 rounded-xl flex flex-col md:flex-row justify-center gap-4">
                        <!-- First Button (Online Appointment) -->
                        @if ($SettingMn::vc($collection, $enumNameSpace::HEADER1_SHOW_BUTTON1))
                            <a href="{{ $SettingMn::vc($collection, $enumNameSpace::HEADER1_BUTTON_HREF1) }}"
                               class="w-full md:w-[190px] h-[36px] bg-[#1766FF] text-white rounded-full text-sm font-semibold flex items-center justify-center gap-2">
                                <span>{{ $SettingMn::vc($collection, $enumNameSpace::HEADER1_BUTTON_TITLE1) }}</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 19l-7-7 7-7"/>
                                </svg>
                            </a>
                        @endif
                        <!-- Second Button (View Doctors List) -->
                        @if ($SettingMn::vc($collection, $enumNameSpace::HEADER1_SHOW_BUTTON2))
                            <a href="{{ $SettingMn::vc($collection, $enumNameSpace::HEADER1_BUTTON_HREF2) }}"
                               class="w-full md:w-[190px] h-[36px] bg-[#00C981] text-white rounded-full text-sm font-semibold flex items-center justify-center gap-2">
                                <span>{{ $SettingMn::vc($collection, $enumNameSpace::HEADER1_BUTTON_TITLE2) }}</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 19l-7-7 7-7"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </section>
    </header>
</div>
@push('styles')
    <style>
        .text-brown {
            color: #AB5E05
        }
    </style>
@endpush
