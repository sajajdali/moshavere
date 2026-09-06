@extends('admin::layouts.app')
@include('onlineconsultation::styles')
@section('content')
<div class="oc-module">
    <header class="oc-header">
        <div class="oc-heading">
            <div class="oc-heading-icon"><i class="fa-solid fa-headset" aria-hidden="true"></i></div>
            <div>
                <p class="oc-eyebrow">مشاوره آنلاین</p>
                <h1 class="oc-title">@yield('consultation-title', 'داشبورد مشاوره')</h1>
                <p class="oc-subtitle">@yield('consultation-description', 'مدیریت مشاوره صوتی، پزشکان و کارشناسان')</p>
            </div>
        </div>
        @yield('consultation-header-actions')
    </header>
    <nav class="oc-tabs" aria-label="بخش‌های مشاوره آنلاین">
        @foreach(['dashboard' => ['داشبورد', 'fa-chart-line'], 'practitioners' => ['پزشکان و کارشناسان', 'fa-user-doctor'], 'settings' => ['تنظیمات مشاوره', 'fa-sliders']] as $key => [$label, $icon])
            <a class="oc-tab" href="{{ route('admin.consultation.'.$key) }}" @if(request()->routeIs('admin.consultation.'.$key.'*')) aria-current="page" @endif>
                <i class="fa-solid {{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span>
            </a>
        @endforeach
    </nav>
    @if(session('success'))
        <div class="oc-notice oc-notice-success" role="status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><p>{{ session('success') }}</p></div>
    @endif
    @if($errors->any())
        <div class="oc-notice oc-notice-error" role="alert">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
            <div><strong>اطلاعات فرم را بررسی کنید.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        </div>
    @endif
    @yield('consultation-content')
</div>
@endsection
