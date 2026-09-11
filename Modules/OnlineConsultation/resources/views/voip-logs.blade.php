@extends('onlineconsultation::shell')
@section('consultation-title', 'لاگ تماس‌های VoIP')
@section('consultation-description', 'ثبت و پیگیری درخواست‌های دریافتی از سرویس تلفنی')
@section('consultation-header-actions')
<a class="oc-btn" href="{{ route('admin.consultation.dashboard') }}"><i class="fa-solid fa-arrow-right"></i>بازگشت</a>
@endsection
@section('consultation-content')
<section class="oc-panel"><div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-list"></i>درخواست‌های ثبت‌شده</h2><form class="oc-search" method="GET"><div class="oc-field"><input class="oc-input" name="phone" value="{{ $phone }}" placeholder="جست‌وجوی شماره"></div><button class="oc-btn" type="submit">جست‌وجو</button></form></div>
@if($logs->isEmpty())<div class="oc-empty"><i class="fa-solid fa-phone-slash"></i><h3>لاگی پیدا نشد</h3><p>درخواست‌های API ویپ پس از دریافت، در این فهرست نمایش داده می‌شوند.</p></div>@else
<div class="oc-table-wrap" role="region" aria-label="لاگ درخواست‌های ویپ" tabindex="0"><table class="oc-table"><thead><tr><th>زمان</th><th>شماره</th><th>مسیر</th><th>کد نتیجه</th><th>پاسخ</th><th>زمان پاسخ</th><th>IP</th></tr></thead><tbody>
@foreach($logs as $log)<tr><td><bdi>{{ verta($log->created_at)->format('Y/m/d H:i:s') }}</bdi></td><td><bdi>{{ $log->phone ?: '—' }}</bdi></td><td><bdi>{{ $log->method }} {{ $log->path }}</bdi></td><td><span class="oc-badge {{ $log->error_code === 0 ? 'oc-badge-success' : '' }}">{{ $log->error_code ?? '—' }}</span></td><td>{{ $log->response_status ?: '—' }}</td><td>{{ $log->duration_ms !== null ? $log->duration_ms.' ms' : '—' }}</td><td><bdi>{{ $log->client_ip ?: '—' }}</bdi></td></tr>@endforeach
</tbody></table></div>{{ $logs->links('onlineconsultation::components.pagination') }}@endif</section>
@endsection
