<!doctype html>
<html lang="fa_IR" dir="rtl"
    style="--primary01:rgba(0, 112, 187, 0.1); --primary02:rgba(0, 112, 187, 0.2); --primary03:rgba(0, 112, 187, 0.3); --primary06:rgba(0, 112, 187, 0.6); --primary09:rgba(0, 112, 187, 0.9); --primary-bg-color:#0070bb; --primary-bg-hover:#0070bb95; --primary-bg-border:#0070bb; --dark-null:rgba(0, 112, 187, 0.5); --transparent-null:#0070bb; --primary-transparentcolor:#0070bb20; --darkprimary-null:#0070bb20; --transparentprimary-null:#0070bb20;">

<head>

    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="{{ setting(Modules\Setting\Enum\SettingKeyEnum::SITE_TITLE) }}">
    <meta name="author" content="شمیران وب">
    <meta name="keywords" content="{{ setting(Modules\Setting\Enum\SettingKeyEnum::SITE_TITLE) }}">
    <!-- Favicon -->
    {{-- <link rel="apple-touch-icon" sizes="76x76" href="{{ admin_assets('favicon/apple-touch-icon.png') }}"> --}}
    {{-- <link rel="icon" type="image/png" sizes="32x32" href="{{ front_default_asset('favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ front_default_asset('favicon/favicon-16x16.png') }}"> --}}
    {{-- <link rel="manifest" href="{{ front_default_asset('favicon/site.webmanifest') }}"> --}}
    {{-- <link rel="mask-icon" href="{{ front_default_asset('favicon/safari-pinned-tab.svg') }}" color="#5bbad5">
    <link rel="mask-icon" href="{{ front_default_asset('favicon/safari-pinned-tab.svg') }}" color="#5bbad5"> --}}
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">
    <!-- TITLE -->
    <title>{{ setting(Modules\Setting\Enum\SettingKeyEnum::SITE_TITLE) }} @isset($title)
            | {{ $title }}
        @endisset </title>
        @stack('styles')
    @include('admin::layouts.components.styles')
    @livewireStyles
</head>

<body class="rtl app sidebar-mini px-md-5">
    <!-- PAGE -->
    <div class="page">
        <div class="page-main px-md-5 mt-5">
            <!--app-content open-->
            <div class="main-container container-fluid px-5">
                @yield('content')
                {{ $slot ?? '' }}
            </div>
        </div>

        @yield('modal')

    </div>
    <!-- page -->
    @include('front::layouts.components.scripts')
    @livewireScripts
    @stack('scripts')

</body>

</html>
