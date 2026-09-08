@php
    $supportTenant = tenancy()->initialized ? tenant() : null;
    $supportStartedAt = $supportTenant?->support_started_at;
    $supportExpiresAt = $supportTenant?->expires_at;
    $supportDaysLeft = $supportExpiresAt
        ? (int) now()->startOfDay()->diffInDays($supportExpiresAt->copy()->startOfDay(), false)
        : null;
    $supportState = $supportDaysLeft === null
        ? 'unknown'
        : ($supportDaysLeft < 0 ? 'expired' : ($supportDaysLeft <= 30 ? 'warning' : 'active'));
    $supportStateLabel = match ($supportState) {
        'expired' => 'پشتیبانی منقضی شده',
        'warning' => 'نزدیک به پایان',
        'active' => 'پشتیبانی فعال',
        default => 'تاریخ تعیین نشده',
    };
@endphp

<details class="tenant-support-card tenant-support-card--{{ $supportState }} mb-4">
    <summary class="tenant-support-card__summary" aria-label="نمایش وضعیت پشتیبانی">
        <span class="tenant-support-card__compact-icon"><i class="fa fa-shield" aria-hidden="true"></i></span>
        <span class="tenant-support-card__compact-copy">
            <small>وضعیت پشتیبانی</small>
            @if ($supportDaysLeft !== null)
                <strong>{{ $supportDaysLeft > 0 ? $supportDaysLeft : ($supportDaysLeft === 0 ? 'امروز' : 'پایان‌یافته') }}</strong>
                @if ($supportDaysLeft > 0)<span>روز</span>@endif
            @else
                <strong>—</strong>
            @endif
        </span>
        <span class="tenant-support-card__chevron"><i class="fa fa-angle-down"></i></span>
    </summary>
    <div class="tenant-support-card__panel">
        <div class="tenant-support-card__glow"></div>
        <div class="tenant-support-card__content">
            <div class="tenant-support-card__icon"><i class="fa fa-shield" aria-hidden="true"></i></div>
            <div class="tenant-support-card__main">
                <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                    <h3 class="tenant-support-card__title mb-0">وضعیت پشتیبانی سایت</h3>
                    <span class="tenant-support-card__status">{{ $supportStateLabel }}</span>
                </div>
                <div class="tenant-support-card__dates">
                    <div><small>شروع پشتیبانی</small><strong>{{ $supportStartedAt ? verta($supportStartedAt)->format('Y/m/d') : '—' }}</strong></div>
                    <span class="tenant-support-card__arrow"><i class="fa fa-long-arrow-left"></i></span>
                    <div><small>پایان پشتیبانی</small><strong>{{ $supportExpiresAt ? verta($supportExpiresAt)->format('Y/m/d') : '—' }}</strong></div>
                </div>
            </div>
            <a href="{{ route('admin.tenant-renew') }}" class="tenant-support-card__renew">مشاهده و تمدید <i class="fa fa-arrow-left"></i></a>
        </div>
    </div>
</details>

@push('styles')
<style>
    .tenant-support-card { --support-accent:#16a34a; --support-soft:#dcfce7; width:max-content; max-width:100%; border:1px solid rgba(22,163,74,.18); border-radius:14px; background:#fff; box-shadow:0 5px 18px rgba(15,23,42,.06); transition:width .2s ease,box-shadow .2s ease; }
    .tenant-support-card--warning { --support-accent:#d97706; --support-soft:#fef3c7; border-color:rgba(217,119,6,.22); background:linear-gradient(125deg,#fff 5%,#fffbeb 100%); }
    .tenant-support-card--expired { --support-accent:#dc2626; --support-soft:#fee2e2; border-color:rgba(220,38,38,.22); background:linear-gradient(125deg,#fff 5%,#fef2f2 100%); }
    .tenant-support-card--unknown { --support-accent:#64748b; --support-soft:#e2e8f0; border-color:#e2e8f0; background:linear-gradient(125deg,#fff 5%,#f8fafc 100%); }
    .tenant-support-card[open] { width:100%; box-shadow:0 12px 32px rgba(15,23,42,.08); }
    .tenant-support-card__summary { display:flex; align-items:center; gap:10px; min-width:190px; padding:8px 11px; cursor:pointer; list-style:none; user-select:none; }
    .tenant-support-card__summary::-webkit-details-marker { display:none; }
    .tenant-support-card__compact-icon { display:grid; place-items:center; width:34px; height:34px; flex:0 0 34px; border-radius:10px; color:var(--support-accent); background:var(--support-soft); font-size:15px; }
    .tenant-support-card__compact-copy { display:flex; align-items:baseline; gap:4px; flex:1; white-space:nowrap; }
    .tenant-support-card__compact-copy small { color:#64748b; font-size:10px; }
    .tenant-support-card__compact-copy strong { color:var(--support-accent); font-size:19px; font-weight:900; }
    .tenant-support-card__compact-copy span { color:#64748b; font-size:10px; }
    .tenant-support-card__chevron { color:#94a3b8; font-size:14px; transition:transform .2s ease; }
    .tenant-support-card[open] .tenant-support-card__chevron { transform:rotate(180deg); }
    .tenant-support-card__panel { position:relative; overflow:hidden; border-top:1px solid rgba(100,116,139,.12); }
    .tenant-support-card__glow { position:absolute; width:220px; height:220px; border-radius:50%; background:var(--support-accent); opacity:.07; top:-130px; left:-60px; }
    .tenant-support-card__content { position:relative; display:flex; align-items:center; gap:20px; padding:24px 28px; }
    .tenant-support-card__icon { display:flex; align-items:center; justify-content:center; flex:0 0 58px; width:58px; height:58px; border-radius:17px; color:var(--support-accent); background:var(--support-soft); font-size:25px; }
    .tenant-support-card__main { flex:1; min-width:0; }
    .tenant-support-card__title { color:#172033; font-size:17px; font-weight:800; }
    .tenant-support-card__status { padding:5px 10px; border-radius:999px; color:var(--support-accent); background:var(--support-soft); font-size:11px; font-weight:700; }
    .tenant-support-card__dates { display:flex; align-items:center; gap:18px; }
    .tenant-support-card__dates div { display:flex; flex-direction:column; gap:2px; }
    .tenant-support-card__dates small { color:#64748b; font-size:11px; }
    .tenant-support-card__dates strong { color:#25324a; font-size:15px; font-weight:800; direction:ltr; }
    .tenant-support-card__arrow { color:#94a3b8; }
    .tenant-support-card__renew { display:inline-flex; align-items:center; gap:8px; padding:10px 16px; border-radius:10px; color:#fff !important; background:var(--support-accent); font-size:12px; font-weight:700; text-decoration:none !important; box-shadow:0 5px 14px color-mix(in srgb,var(--support-accent) 25%,transparent); }
    @media (max-width:767.98px) { .tenant-support-card__content { align-items:flex-start; flex-wrap:wrap; padding:18px; } .tenant-support-card__main { flex-basis:calc(100% - 78px); } .tenant-support-card__renew { margin-right:auto; } }
</style>
@endpush
