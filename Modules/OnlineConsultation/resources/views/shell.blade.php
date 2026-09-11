@extends('admin::layouts.app')
@include('onlineconsultation::styles')
@section('content')
<div class="oc-page-loader" id="oc-page-loader" aria-hidden="true" aria-live="polite">
    <div class="oc-loader-card" role="status">
        <div class="oc-loader-visual"><span></span><span></span><span></span><i class="fa-solid fa-headset" aria-hidden="true"></i></div>
        <strong>در حال آماده‌سازی اطلاعات</strong>
        <small>لطفاً چند لحظه صبر کنید…</small>
        <div class="oc-loader-progress"><span></span></div>
    </div>
</div>
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
        @foreach(['dashboard' => ['داشبورد', 'fa-chart-line', 'dashboard*'], 'consultants-dashboard.index' => ['داشبورد مشاوران تلفنی', 'fa-headset', 'consultants-dashboard.*'], 'financial-report.index' => ['گزارش جامع مالی', 'fa-coins', 'financial-report.*'], 'practitioners' => ['پزشکان و کارشناسان', 'fa-user-doctor', 'practitioners*'], 'call-reports.index' => ['گزارش تماس‌ها', 'fa-chart-column', 'call-reports.*'], 'sms-reminders.index' => ['یادآوری پیامکی', 'fa-comment-sms', 'sms-reminders.*'], 'settings' => ['تنظیمات مشاوره', 'fa-sliders', 'settings*']] as $key => [$label, $icon, $active])
            <a class="oc-tab" href="{{ route('admin.consultation.'.$key) }}" @if(request()->routeIs('admin.consultation.'.$active)) aria-current="page" @endif>
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
@push('scripts')
<script>
(function () {
    const loader = document.getElementById('oc-page-loader');
    if (!loader) return;
    const show = () => { loader.classList.add('is-visible'); loader.setAttribute('aria-hidden', 'false'); document.body.classList.add('oc-is-loading'); };
    const hide = () => { loader.classList.remove('is-visible'); loader.setAttribute('aria-hidden', 'true'); document.body.classList.remove('oc-is-loading'); };

    document.addEventListener('click', function (event) {
        const link = event.target.closest('.oc-module a[href]');
        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || link.target === '_blank' || link.hasAttribute('download') || href.includes('/export')) return;
        show();
    });
    document.addEventListener('submit', function (event) {
        if (!event.target.closest('.oc-module') || event.defaultPrevented || event.submitter?.name === 'export') return;
        show();
    });
    window.addEventListener('pageshow', hide);
    window.addEventListener('load', hide);
    window.addEventListener('beforeunload', show);
})();
</script>
@endpush
