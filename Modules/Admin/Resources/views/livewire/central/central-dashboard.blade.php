<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">مدیریت سایت‌ها</h1>
            <p class="text-muted mb-0">تعریف و مشاهده سایت‌های مستقل سامانه</p>
        </div>
        <div class="ms-auto pageheader-btn">
            <a href="{{ route('central.new_site.create') }}" class="btn btn-primary">
                <i class="fa fa-plus-circle me-1"></i> افزودن سایت جدید
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card"><div class="card-body">
                <h3 class="mb-2 fw-semibold">{{ $tenants->count() }}</h3>
                <p class="text-muted mb-0">کل سایت‌ها</p>
            </div></div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card"><div class="card-body">
                <h3 class="mb-2 fw-semibold text-success">{{ $activeCount }}</h3>
                <p class="text-muted mb-0">سایت‌های فعال</p>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom"><h3 class="card-title">فهرست سایت‌ها</h3></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr>
                        <th>نام سایت</th><th>مشتری</th><th>شناسه</th><th>دامنه</th><th>پشتیبانی</th><th>ماژول‌ها</th><th>وضعیت</th><th>عملیات</th>
                    </tr></thead>
                    <tbody>
                    @forelse ($tenants as $tenant)
                        <tr>
                            <td>{{ $tenant->name ?: 'بدون نام' }}</td>
                            <td>
                                @php
                                    $customer = $customersById->get($tenant->customer_id);
                                @endphp
                                {{ $customer?->full_name ?: '—' }}
                                @if ($customer?->center_name)<small class="text-muted d-block">{{ $customer->center_name }}</small>@endif
                            </td>
                            <td dir="ltr">{{ $tenant->id }}</td>
                            <td dir="ltr">
                                @foreach ($tenant->domains as $domain)
                                    <div>{{ $domain->domain }}</div>
                                @endforeach
                            </td>
                            <td>
                                @php
                                    $daysLeft = $tenant->expires_at
                                        ? (int) now()->startOfDay()->diffInDays($tenant->expires_at->copy()->startOfDay(), false)
                                        : null;
                                    $supportBadge = $daysLeft === null
                                        ? 'bg-secondary'
                                        : ($daysLeft < 0 ? 'bg-danger' : ($daysLeft <= 30 ? 'bg-warning text-dark' : 'bg-success'));
                                @endphp
                                <small class="d-block">شروع: {{ $tenant->support_started_at ? verta($tenant->support_started_at)->format('Y/m/d') : '—' }}</small>
                                <small class="d-block">پایان: {{ $tenant->expires_at ? verta($tenant->expires_at)->format('Y/m/d') : '—' }}</small>
                                <span class="badge {{ $supportBadge }} mt-1">
                                    @if ($daysLeft === null)
                                        تاریخ نامشخص
                                    @elseif ($daysLeft < 0)
                                        {{ abs($daysLeft) }} روز از پایان گذشته
                                    @elseif ($daysLeft === 0)
                                        امروز تمام می‌شود
                                    @else
                                        {{ $daysLeft }} روز دیگر تمام می‌شود
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if (is_array($tenant->enabled_modules))
                                    {{ count(array_diff($tenant->enabled_modules, \App\Support\TenantModuleAccess::CORE_MODULES)) }} انتخابی
                                @else
                                    همه (قدیمی)
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $tenant->disabled ? 'bg-secondary' : 'bg-success' }}">
                                    {{ $tenant->disabled ? 'غیرفعال' : 'فعال' }}
                                </span>
                            </td>
                            <td><a class="btn btn-sm btn-outline-primary" href="{{ route('central.new_site.edit', $tenant->id) }}"><i class="fa fa-edit"></i> ویرایش</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-5">هنوز سایتی تعریف نشده است.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
