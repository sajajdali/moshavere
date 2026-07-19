<!doctype html>
<html lang="fa_IR" dir="rtl"
    style="--primary01:rgba(0, 112, 187, 0.1); --primary02:rgba(0, 112, 187, 0.2); --primary03:rgba(0, 112, 187, 0.3); --primary06:rgba(0, 112, 187, 0.6); --primary09:rgba(0, 112, 187, 0.9); --primary-bg-color:#0070bb; --primary-bg-hover:#0070bb95; --primary-bg-border:#0070bb; --dark-null:rgba(0, 112, 187, 0.5); --transparent-null:#0070bb; --primary-transparentcolor:#0070bb20; --darkprimary-null:#0070bb20; --transparentprimary-null:#0070bb20;">

<head>

    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="شمیران وب">
    <meta name="keywords" content="{{ setting(Modules\Setting\Enum\SettingKeyEnum::SITE_TITLE) }}">
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="76x76" href="{{ front_asset('/assets/images/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32"
        href="{{ front_asset('/assets/images/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16"
        href="{{ front_asset('/assets/images/favicon/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ front_asset('/assets/images/favicon/site.webmanifest') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">
    <!-- TITLE -->
    @php
        \Artesaos\SEOTools\Facades\SEOTools::setTitle(
            setting(\Modules\Setting\Enum\SettingKeyEnum::SITE_TITLE) ?? 'نوبت دهی',
        );
    @endphp
    {!! SEO::generate(true) !!}

    @include('front::layouts.components.styles')
    @stack('styles')
    @livewireStyles

    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag("consent", "default", {
            ad_storage: "granted",
            ad_user_data: "granted",
            ad_personalization: "granted",
            analytics_storage: "granted",
            functionality_storage: "granted",
            personalization_storage: "granted",
            security_storage: "granted",
            wait_for_update: 2000,
        });
        gtag("set", "ads_data_redaction", true);
        gtag("set", "url_passthrough", true);
    </script>
    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-PGHHCTPG');
    </script>
    <!-- End Google Tag Manager -->
</head>

<body class="rtl app sidebar-mini">
    @php
        $voipOnly = filter_var(
            settingVfc(
                $settingValues,
                \Modules\Setting\Enum\SettingKeyEnum::DISABLE_UI_FOR_VOIP_ONLY_APPOINTMENT,
            ),
            FILTER_VALIDATE_BOOL,
        );
    @endphp
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PGHHCTPG" height="0" width="0"
            style="display:none;visibility:hidden"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->
    @unless ($voipOnly)
        @include('front::layouts.components.app-header', ['settingValues' => $settingValues])
    @endunless
    <!-- Icons Fixed on Left Side -->
    @if (!disableUi())
        @if (settingVfc($settingValues, \Modules\Setting\Enum\SettingKeyEnum::SHOW_FLOATING_SOCIAL_ICONS))
            <div class="fixed-icons">
                <!-- Instagram SVG Icon -->
                @if (settingVfc($settingValues, \Modules\Setting\Enum\SettingKeyEnum::INSTAGRAM_ADDRESS))
                    <a href="{{ setting(\Modules\Setting\Enum\SettingKeyEnum::INSTAGRAM_ADDRESS) }}" target="_blank"
                        class="icon-link instagram">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-instagram" viewBox="0 0 16 16">
                            <path
                                d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334" />
                        </svg>
                    </a>
                @endif

                @if (settingVfc($settingValues, \Modules\Setting\Enum\SettingKeyEnum::WHATSAPP_ADDRESS))
                    <!-- WhatsApp SVG Icon -->
                    <a href="{{ settingVfc($settingValues, \Modules\Setting\Enum\SettingKeyEnum::WHATSAPP_ADDRESS) }}"
                        target="_blank" class="icon-link whatsapp">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-whatsapp" viewBox="0 0 16 16">
                            <path
                                d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.379 2.993.473.203.84.324 1.13.414.475.151.91.13 1.254.079.382-.059 1.17-.477 1.335-.938.165-.46.165-.853.115-.938-.05-.084-.182-.133-.38-.232" />
                        </svg>
                    </a>
                @endif

            </div>
        @endif
    @endif
    <!-- PAGE -->
    <div class="page">
        <div class="page-main bg-secondary-100">
            <!--app-content open-->
            <div class="main-front-container">
                @yield('content')
                @include('front::layouts.components.app-sidebar', ['settingValues' => $settingValues])
                {{ $slot ?? '' }}
            </div>
        </div>

        @yield('modal')

    </div>
    <!-- page -->

    @if (!$voipOnly && !settingVfc($settingValues, \Modules\Setting\Enum\SettingKeyEnum::DISABLE_FOOTER_DISPLAY))
        @include('front::layouts.components.footer', ['settingValues' => $settingValues])
    @endif
    @include('front::layouts.components.scripts')

    @livewireScripts
    @stack('scripts')

</body>

</html>
