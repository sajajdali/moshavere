@extends('onlineconsultation::shell')
@section('consultation-title', 'داشبورد مشاوره آنلاین')
@section('consultation-description', 'نمای کلی مشاوره‌ها و وضعیت پاسخ‌گویی همکاران')
@section('consultation-header-actions')
<a class="oc-btn oc-btn-primary" href="{{ route('admin.consultation.practitioners.create') }}"><i class="fa-solid fa-plus" aria-hidden="true"></i>افزودن پزشک / کارشناس</a>
@endsection
@section('consultation-content')
<div class="oc-notice"><i class="fa-solid fa-circle-info" aria-hidden="true"></i><p>اتصال تماس به سرور ویپ هنوز راه‌اندازی نشده است. تنظیمات و اعضا را آماده کنید؛ وضعیت آماده / مشغول فعلاً دستی است.</p></div>
<div class="oc-stack">
    <div class="oc-stats">
        @foreach([
            ['مشاوره‌های امروز', $todayCount, 'fa-calendar-check', ''],
            ['آماده پاسخ‌گویی', $readyCount, 'fa-user-check', 'oc-stat-green'],
            ['تماس‌های جاری', $activeCalls, 'fa-phone-volume', 'oc-stat-purple'],
            ['بی‌پاسخ‌های امروز', $missedCalls, 'fa-phone-slash', 'oc-stat-orange'],
        ] as [$label, $value, $icon, $tone])
            <div class="oc-panel oc-stat {{ $tone }}"><div class="oc-stat-icon"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></div><div><span>{{ $label }}</span><strong>{{ $value }}</strong></div></div>
        @endforeach
    </div>
    <section class="oc-panel">
        <div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-sliders" aria-hidden="true"></i>وضعیت سرویس</h2><a class="oc-btn oc-btn-small" href="{{ route('admin.consultation.settings') }}">مدیریت تنظیمات</a></div>
        <div class="oc-panel-body"><div class="oc-toolbar">
            <div class="oc-inline">
                <span class="oc-badge {{ $settings->app_enabled ? 'oc-badge-success' : '' }}">اپلیکیشن: {{ $settings->app_enabled ? 'فعال' : 'غیرفعال' }}</span>
                <span class="oc-badge {{ $settings->booking_enabled ? 'oc-badge-success' : '' }}">تنظیم رزرو: {{ $settings->booking_enabled ? 'فعال' : 'غیرفعال' }}</span>
                <span class="oc-badge oc-badge-warning">اتصال ویپ: راه‌اندازی نشده</span>
            </div>
            <a class="oc-btn" href="{{ route('admin.consultation.practitioners') }}">مدیریت پزشکان و کارشناسان<i class="fa-solid fa-arrow-left" aria-hidden="true"></i></a>
        </div></div>
    </section>
    <section class="oc-panel">
        <div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>آخرین مشاوره‌ها</h2></div>
        @if($consultations->isEmpty())
            <div class="oc-empty"><i class="fa-solid fa-headset" aria-hidden="true"></i><h3>هنوز مشاوره‌ای ثبت نشده است</h3><p>پس از راه‌اندازی رزرو و اتصال تماس، مشاوره‌های ثبت‌شده در این بخش نمایش داده می‌شوند.</p><a class="oc-btn" href="{{ route('admin.consultation.settings') }}">آماده‌سازی تنظیمات</a></div>
        @else
            <div class="oc-table-wrap" role="region" aria-label="فهرست مشاوره‌ها" tabindex="0">
                <table class="oc-table"><thead><tr><th scope="col">شناسه</th><th scope="col">پزشک / کارشناس</th><th scope="col">زمان مشاوره</th><th scope="col">وضعیت</th></tr></thead><tbody>
                    @foreach($consultations as $item)
                        <tr><td>{{ $item->id }}</td><td class="oc-person-name">{{ $item->practitioner->display_name }}</td><td><bdi>{{ verta($item->scheduled_at)->format('Y/m/d H:i') }}</bdi></td><td><span class="oc-badge">{{ ['scheduled' => 'رزروشده', 'in_progress' => 'در حال مشاوره', 'completed' => 'پایان‌یافته', 'cancelled' => 'لغوشده', 'missed' => 'بی‌پاسخ'][$item->status] ?? $item->status }}</span></td></tr>
                    @endforeach
                </tbody></table>
            </div>
        @endif
        @if($consultations->hasPages())<div class="oc-pagination">{{ $consultations->links() }}</div>@endif
    </section>
</div>
@endsection
