@extends('admin::layouts.central.app')
@include('onlineconsultation::styles')
@section('content')
<div class="oc-module">
    <header class="oc-header"><div class="oc-heading"><div class="oc-heading-icon"><i class="fa-solid fa-headset" aria-hidden="true"></i></div><div><p class="oc-eyebrow">پنل مرکزی · مدیریت ماژول‌ها</p><h1 class="oc-title">مشاوره آنلاین</h1><p class="oc-subtitle">مدیریت دسترسی سایت‌ها به ماژول مشاوره آنلاین</p></div></div></header>
    @if(session('success'))<div class="oc-notice oc-notice-success" role="status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><p>{{ session('success') }}</p></div>@endif
    @if($errors->any())<div class="oc-notice oc-notice-error" role="alert">{{ $errors->first() }}</div>@endif
    <div class="oc-notice"><i class="fa-solid fa-circle-info" aria-hidden="true"></i><p>ماژول فقط برای سایت‌های فعال‌شده در دسترس است. غیرفعال‌کردن، دسترسی پنل و اپلیکیشن را می‌بندد و اطلاعات قبلی را حفظ می‌کند.</p></div>
    <section class="oc-panel">
        <div class="oc-panel-header"><h2 class="oc-panel-title">دسترسی سایت‌ها<span class="oc-badge">{{ $tenants->total() }} سایت</span></h2></div>
        @if($tenants->isEmpty())
            <div class="oc-empty"><i class="fa-solid fa-globe" aria-hidden="true"></i><h3>هنوز سایتی ثبت نشده است</h3><p>پس از ایجاد سایت، دسترسی مشاوره آنلاین آن را از این بخش مدیریت کنید.</p></div>
        @else
            <div class="oc-table-wrap" role="region" aria-label="دسترسی ماژول سایت‌ها" tabindex="0">
                <table class="oc-table"><thead><tr><th scope="col">شناسه سایت</th><th scope="col">دامنه‌ها</th><th scope="col">وضعیت ماژول</th><th scope="col">تغییر دسترسی</th></tr></thead><tbody>
                    @foreach($tenants as $site)
                        <tr><td><bdi>{{ $site->id }}</bdi></td><td>@foreach($site->domains as $domain)<div><bdi>{{ $domain->domain }}</bdi></div>@endforeach</td><td><span class="oc-badge {{ $site->online_consultation_enabled ? 'oc-badge-success' : '' }}">{{ $site->online_consultation_enabled ? 'فعال' : 'غیرفعال' }}</span></td><td><form method="POST" action="{{ route('central.consultation.update', $site->id) }}">@csrf @method('PUT')<input type="hidden" name="enabled" value="{{ $site->online_consultation_enabled ? 0 : 1 }}"><button class="oc-btn oc-btn-small {{ $site->online_consultation_enabled ? 'oc-btn-danger' : 'oc-btn-primary' }}" type="submit">{{ $site->online_consultation_enabled ? 'غیرفعال‌کردن' : 'فعال‌کردن برای این سایت' }}</button></form></td></tr>
                    @endforeach
                </tbody></table>
            </div>
        @endif
        @if($tenants->hasPages())<div class="oc-pagination">{{ $tenants->links() }}</div>@endif
    </section>
</div>
@endsection
