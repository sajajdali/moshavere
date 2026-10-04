{{-- Favicon: uploaded icons from site settings, falling back to the default icons --}}
@php
    $favicons = site_favicons($settingValues ?? null);
@endphp
<link rel="apple-touch-icon" sizes="{{ $favicons['180x180'] ? '180x180' : '76x76' }}"
    href="{{ $favicons['180x180'] ? assetStorage($favicons['180x180']) : asset('default/admin/favicon/apple-touch-icon.png') }}">
<link rel="icon" sizes="32x32"
    href="{{ $favicons['32x32'] ? assetStorage($favicons['32x32']) : asset('default/admin/favicon/favicon-32x32.png') }}">
<link rel="icon" sizes="16x16"
    href="{{ $favicons['16x16'] ? assetStorage($favicons['16x16']) : asset('default/admin/favicon/favicon-16x16.png') }}">
