<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ setting(\Modules\Setting\Enum\SettingKeyEnum::SITE_TITLE) ?? 'نوبت دهی' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ admin_asset('plugins/persiandate/persian-datepicker.min.css') }}">
    <script src="{{ admin_asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/persiandate/persian-date.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/persiandate/persian-datepicker.min.js') }}"></script>
    @vite(['resources/js/newapp/main.jsx'])
</head>

<body>
    {{-- داده اولیه تا اپ بدون یک رفت و برگشت اضافه بالا بیاید --}}
    <div id="newapp-root" data-bootstrap="{{ json_encode($bootstrap, JSON_UNESCAPED_UNICODE) }}"></div>

    <noscript>
        <div style="padding:32px;text-align:center;font-family:Vazirmatn,system-ui,sans-serif">
            برای استفاده از این سایت، لطفا جاوااسکریپت مرورگر خود را فعال کنید.
        </div>
    </noscript>
</body>

</html>
