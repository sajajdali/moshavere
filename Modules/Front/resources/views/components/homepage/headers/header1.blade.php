<div>
    <!-- header -->
    <header class="relative bg-primary-main pt-1 space-y-10 md:space-y-16">
        <!-- Background Pattern (Blue with SVG pattern) -->
        <div class="absolute inset-0 bg-cover bg-repeat"
             style="background-image: url({{asset('storage/header/header_bg.svg')}});"></div>

        <section class="relative w-full container mx-auto px-5 flex flex-col md:flex-row items-center gap-6 md:gap-8">
            <!-- Right Section: Texts -->
            <div class="md:w-1/2 w-full text-right">
                @if(setting(\Modules\Setting\Enum\SettingKeyEnum::HEADER1_TITLE1))
                    <h1 class="text-2xl md:text-3xl text-white font-bold mb-4">
                        {{setting(\Modules\Setting\Enum\SettingKeyEnum::HEADER1_TITLE1)}}
                    </h1>
                @endif

                @if(setting(\Modules\Setting\Enum\SettingKeyEnum::HEADER1_TITLE1))
                    <p class="text-white text-sm md:text-base mb-6">
                        {{setting(\Modules\Setting\Enum\SettingKeyEnum::HEADER1_TITLE2)}}
                    </p>
                @endif

                @if(setting(\Modules\Setting\Enum\SettingKeyEnum::HEADER1_SHOW_BUTTON1) ||  setting(\Modules\Setting\Enum\SettingKeyEnum::HEADER1_SHOW_BUTTON2))
                    <div
                        class="w-full max-w-[450px] mx-auto bg-white p-6 rounded-xl flex flex-col md:flex-row justify-center gap-4">
                        <!-- First Button (Online Appointment) -->
                        @if(setting(\Modules\Setting\Enum\SettingKeyEnum::HEADER1_SHOW_BUTTON1))
                            <a href="{{setting(\Modules\Setting\Enum\SettingKeyEnum::HEADER1_BUTTON_HREF1)}}"
                               class="w-full md:w-[190px] h-[36px] bg-[#1766FF] text-white rounded-full text-sm font-semibold flex items-center justify-center gap-2">
                                <span>{{setting(\Modules\Setting\Enum\SettingKeyEnum::HEADER1_BUTTON_TITLE1)}}</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 19l-7-7 7-7"/>
                                </svg>
                            </a>
                        @endif
                        <!-- Second Button (View Doctors List) -->
                        @if(setting(\Modules\Setting\Enum\SettingKeyEnum::HEADER1_SHOW_BUTTON2))
                            <a href="{{setting(\Modules\Setting\Enum\SettingKeyEnum::HEADER1_BUTTON_HREF2)}}"
                               class="w-full md:w-[190px] h-[36px] bg-[#00C981] text-white rounded-full text-sm font-semibold flex items-center justify-center gap-2">
                                <span>{{setting(\Modules\Setting\Enum\SettingKeyEnum::HEADER1_BUTTON_TITLE2)}}</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 19l-7-7 7-7"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Left Section: Doctor's Image (on Desktop) -->
            <div class="md:w-1/2 w-full flex justify-end relative md:static absolute bottom-0 left-0">
                @if(setting(\Modules\Setting\Enum\SettingKeyEnum::HEADER1_IMAGE))
                    <div class="w-auto h-auto max-w-[400px]">
                        <img src="{{assetStorage(setting(\Modules\Setting\Enum\SettingKeyEnum::HEADER1_IMAGE))}}"
                             alt="Doctor's Image" class="w-full h-auto object-contain">
                    </div>
                @endif
            </div>
        </section>
    </header>
</div>
