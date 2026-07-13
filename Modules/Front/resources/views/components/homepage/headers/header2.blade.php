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
            $enumNameSpace::HEADER2_MOBILE_BACKGROUND_IMAGE,
            $enumNameSpace::HEADER1_TITLE_COLOR,
            $enumNameSpace::HEADER1_TITLE_NOT_SHOW_MOBILE,
            $enumNameSpace::HEADER1_TITLE_NOT_SHOW_DESKTOP,
        ])->get();
        $headerTextColor = 'text-' . $SettingMn::vc($collection, $enumNameSpace::HEADER1_TITLE_COLOR) ?? 'white';
        $hideHeaderTitlesMobile = filter_var(
            $SettingMn::vc($collection, $enumNameSpace::HEADER1_TITLE_NOT_SHOW_MOBILE),
            FILTER_VALIDATE_BOOLEAN
        );
        $hideHeaderTitlesDesktop = filter_var(
            $SettingMn::vc($collection, $enumNameSpace::HEADER1_TITLE_NOT_SHOW_DESKTOP),
            FILTER_VALIDATE_BOOLEAN
        );
        // dd($hideHeaderTitlesMobile,$hideHeaderTitlesDesktop);
        $headerTitle1 = $SettingMn::vc($collection, $enumNameSpace::HEADER1_TITLE1);
        $headerTitle2 = $SettingMn::vc($collection, $enumNameSpace::HEADER1_TITLE2);
        $headerTitlesVisibilityClass = match (true) {
            $hideHeaderTitlesMobile && $hideHeaderTitlesDesktop => 'hidden',
            $hideHeaderTitlesMobile => 'hidden md:block',
            $hideHeaderTitlesDesktop => 'md:hidden',
            default => '',
        };
        // dd($headerTitlesVisibilityClass);
        $headerDesktopBackground = $SettingMn::vc($collection, $enumNameSpace::HEADER1_IMAGE);
        $headerMobileBackground = $SettingMn::vc($collection, $enumNameSpace::HEADER2_MOBILE_BACKGROUND_IMAGE);
        $headerDesktopBackgroundUrl = assetStorage($headerDesktopBackground);
        $headerMobileBackgroundUrl = $headerMobileBackground ? assetStorage($headerMobileBackground) : null;
        $headerBackgroundStyles = "--header2-desktop-bg: url('{$headerDesktopBackgroundUrl}');";
        $headerBackgroundStyles .= $headerMobileBackgroundUrl ? " --header2-mobile-bg: url('{$headerMobileBackgroundUrl}');" : '';
    @endphp
    <style>
        .header2 {
            min-height: 300px;
            background-color: transparent !important;
            background-image: var(--header2-desktop-bg);
            background-position: center top;
            background-repeat: repeat;
            background-size: cover;
        }

        .header2-content {
            min-height: 300px;
            padding-top: 48px;
            padding-bottom: 48px;
        }

        .header2-title {
            text-shadow:
                0 1px 2px rgba(0, 0, 0, 0.85),
                0 3px 10px rgba(0, 0, 0, 0.55);
        }

        @media (max-width: 767.98px) {
            .header2 {
                min-height: 360px;
                background-image: var(--header2-mobile-bg, var(--header2-desktop-bg)) !important;
                background-position: center top;
            }

            .header2-content {
                min-height: 360px;
                padding-top: 72px;
                padding-bottom: 72px;
            }
        }
    </style>
    <!-- header -->
    <header class="header2 relative bg-primary-main pt-1 space-y-10 md:space-y-16"
            style="{{ $headerBackgroundStyles }}">
        <section class="header2-content relative w-full container mx-auto px-5 flex flex-col md:flex-row items-center gap-6 md:gap-8">
            <!-- Right Section: Texts -->
            <div class="md:w-1/2 w-full text-right">
                @if ($headerTitle1)
                    <h1 class="header2-title text-2xl md:text-3xl {{ $headerTextColor }} font-bold mb-4 {{ $headerTitlesVisibilityClass }}">
                        {{ $headerTitle1 }}
                    </h1>
                @endif

                @if ($headerTitle2)
                    <p class="header2-title {{ $headerTextColor }} text-sm md:text-base mb-6 {{ $headerTitlesVisibilityClass }}">
                        {{ $headerTitle2 }}
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
