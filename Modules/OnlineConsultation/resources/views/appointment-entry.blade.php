@if(\Module::isEnabled('OnlineConsultation') && \Modules\OnlineConsultation\Support\ConsultationAccess::enabled())
@can('ONLINE_CONSULTATION_MANAGE')
<div class="card mb-4"><div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3"><div><h2 class="card-title mb-2">مشاوره آنلاین</h2><p class="text-muted mb-0">تعرفه، زمان جلسه، برنامه هفتگی و داخلی پزشکان را در ماژول مشاوره مدیریت کنید.</p></div><div class="d-flex flex-wrap gap-2">@if(isset($doctor) || isset($fetchData['doctor']))<a class="btn btn-outline-primary" href="{{ route('admin.consultation.practitioners.create', ['user' => $doctor->id ?? $fetchData['doctor']->id ?? null]) }}">تنظیم مشاوره این پزشک</a>@endif<a class="btn btn-outline-secondary" href="{{ route('admin.consultation.settings') }}">تنظیمات عمومی مشاوره</a></div></div></div>
@endcan
@endif
