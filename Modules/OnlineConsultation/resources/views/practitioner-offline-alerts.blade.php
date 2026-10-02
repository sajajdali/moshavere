@extends('onlineconsultation::shell')
@section('consultation-title', 'هشدار پزشکان آفلاین')
@section('consultation-description', 'گزارش بررسی سه دقیقه مانده به نوبت و تماس خودکار برای ورود پزشک به اپلیکیشن')
@section('consultation-content')
@php($labels=['accepted'=>'تماس ثبت شد','failed'=>'تماس ناموفق','check_failed'=>'بررسی وضعیت ناموفق'])
<section class="oc-panel">
    <div class="oc-panel-header">
        <div><h2 class="oc-panel-title"><i class="fa-solid fa-bell"></i>پزشکان آفلاین پیش از مشاوره <span class="oc-badge">{{ $alerts->total() }}</span></h2><p class="oc-help">هر ردیف مربوط به یک نوبت است و تماس تکراری برای همان نوبت ایجاد نمی‌شود.</p></div>
        <form method="GET" class="oc-inline">
            <input class="oc-input" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="نام، موبایل یا داخلی">
            <select class="oc-input" name="status"><option value="">همه وضعیت‌ها</option>@foreach($labels as $value=>$label)<option value="{{ $value }}" @selected(($filters['status']??'')===$value)>{{ $label }}</option>@endforeach</select>
            <button class="oc-btn" type="submit">فیلتر</button>
        </form>
    </div>
    <div class="oc-table-wrap"><table class="oc-table"><thead><tr><th>پزشک</th><th>نوبت</th><th>داخلی / موبایل</th><th>زمان بررسی</th><th>Route</th><th>وضعیت</th><th>پاسخ / خطا</th><th></th></tr></thead><tbody>
    @forelse($alerts as $alert)
        <tr class="{{ in_array($alert->status,['failed','check_failed'],true)?'oc-row-danger':'' }}">
            <td><strong>{{ $alert->practitioner?->display_name ?: '—' }}</strong></td>
            <td>#{{ $alert->appointment?->tracking_code ?: $alert->appointment_id }}<small class="oc-cell-sub">{{ $alert->appointment?->date_visit ? verta($alert->appointment->date_visit)->format('Y/m/d H:i') : '—' }}</small></td>
            <td><bdi class="oc-ltr">داخلی {{ $alert->extension }}</bdi><small class="oc-cell-sub oc-ltr">{{ $alert->phone }}</small></td>
            <td><bdi>{{ verta($alert->checked_at)->format('Y/m/d H:i:s') }}</bdi>@if($alert->sent_at)<small class="oc-cell-sub">ارسال: {{ verta($alert->sent_at)->format('H:i:s') }}</small>@endif</td>
            <td class="oc-ltr">{{ $alert->route_name }}</td>
            <td><span class="oc-badge {{ $alert->status==='accepted'?'oc-badge-success':'oc-badge-danger' }}">{{ $labels[$alert->status]??$alert->status }}</span><small class="oc-cell-sub">HTTP {{ $alert->http_status ?: '—' }}</small></td>
            <td>{{ $alert->error_message ?: data_get($alert->response_payload,'message') ?: $alert->response_body ?: 'درخواست پذیرفته شد.' }}</td>
            <td><a class="oc-btn oc-btn-small" href="{{ route('admin.consultation.call-reports.appointment',$alert->appointment_id) }}">نوبت</a></td>
        </tr>
    @empty<tr><td colspan="8"><div class="oc-empty"><i class="fa-solid fa-user-check"></i><h3>هنوز پزشک آفلاینی ثبت نشده است</h3><p>پس از اجرای بررسی خودکار، نتیجه‌ها در این قسمت نمایش داده می‌شوند.</p></div></td></tr>@endforelse
    </tbody></table></div>
    {{ $alerts->links('onlineconsultation::components.pagination') }}
</section>
@endsection
