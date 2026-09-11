@extends('onlineconsultation::shell')
@section('consultation-title', 'پزشکان و کارشناسان')
@section('consultation-description', 'تعریف همکاران، تنظیم داخلی و مدیریت دسترسی اپلیکیشن')
@section('consultation-header-actions')
<a class="oc-btn oc-btn-primary" href="{{ route('admin.consultation.practitioners.create') }}"><i class="fa-solid fa-plus" aria-hidden="true"></i>افزودن پزشک / کارشناس</a>
@endsection
@section('consultation-content')
<section class="oc-panel">
    <div class="oc-panel-header">
        <h2 class="oc-panel-title">فهرست همکاران<span class="oc-badge">{{ $people->total() }} نفر</span></h2>
        <form method="GET" action="{{ route('admin.consultation.practitioners') }}" class="oc-search" role="search">
            <div class="oc-field"><input class="oc-input" name="search" value="{{ $search }}" aria-label="جست‌وجوی نام یا داخلی" placeholder="جست‌وجوی نام یا داخلی" maxlength="100"></div>
            <button class="oc-btn" type="submit" aria-label="جست‌وجو"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
            @if($search !== '')<a class="oc-btn" href="{{ route('admin.consultation.practitioners') }}">پاک‌کردن</a>@endif
        </form>
    </div>
    @if($people->isEmpty())
        <div class="oc-empty">
            <i class="fa-solid fa-user-doctor" aria-hidden="true"></i>
            <h3>{{ $search !== '' ? 'نتیجه‌ای برای جست‌وجوی شما پیدا نشد' : 'هنوز همکاری اضافه نشده است' }}</h3>
            <p>{{ $search !== '' ? 'نام یا شماره داخلی دیگری جست‌وجو کنید.' : 'با افزودن پزشک یا کارشناس، داخلی، دسترسی اپلیکیشن و برنامه حضور او را تنظیم کنید.' }}</p>
            <a class="oc-btn" href="{{ $search !== '' ? route('admin.consultation.practitioners') : route('admin.consultation.practitioners.create') }}">{{ $search !== '' ? 'نمایش همه همکاران' : 'افزودن اولین همکار' }}</a>
        </div>
    @else
        <div class="oc-table-wrap" role="region" aria-label="فهرست پزشکان و کارشناسان" tabindex="0">
            <table class="oc-table"><thead><tr><th scope="col">نام همکار</th><th scope="col">نوع / تخصص</th><th scope="col">داخلی</th><th scope="col">وضعیت</th><th scope="col">اپلیکیشن</th><th scope="col">مدیریت</th></tr></thead><tbody>
                @foreach($people as $person)
                    <tr>
                        <td class="oc-person-name">{{ $person->display_name }}</td>
                        <td>{{ $person->kind === 'doctor' ? 'پزشک' : 'کارشناس' }}<small class="oc-help">{{ $person->specialty ?: 'تخصص ثبت نشده' }}</small></td>
                        <td><bdi>{{ $person->extension ?: 'تعریف نشده' }}</bdi></td>
                        <td><span class="oc-badge {{ $person->active && $person->availability === 'ready' ? 'oc-badge-success' : ($person->active && $person->availability === 'busy' ? 'oc-badge-warning' : '') }}">{{ !$person->active ? 'غیرفعال' : (['offline' => 'آفلاین', 'ready' => 'آماده', 'busy' => 'مشغول'][$person->availability] ?? 'آفلاین') }}</span></td>
                        <td><span class="oc-badge {{ $person->app_access ? 'oc-badge-success' : '' }}">{{ $person->app_access ? 'مجاز' : 'غیرمجاز' }}</span></td>
                        <td><a class="oc-btn oc-btn-small" href="{{ route('admin.consultation.practitioners.edit', $person) }}"><i class="fa-solid fa-pen" aria-hidden="true"></i>ویرایش</a></td>
                    </tr>
                @endforeach
            </tbody></table>
        </div>
    @endif
    {{ $people->links('onlineconsultation::components.pagination') }}
</section>
@endsection
