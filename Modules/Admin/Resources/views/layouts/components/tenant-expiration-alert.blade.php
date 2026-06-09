@php
    $currentTenant = tenancy()->initialized ? tenant() : null;
    $expiresAt = $currentTenant?->expires_at;
    $daysUntilExpiration = $expiresAt
        ? (int) now()
            ->startOfDay()
            ->diffInDays($expiresAt->copy()->startOfDay(), false)
        : null;
    $accessExpired = $daysUntilExpiration !== null && $daysUntilExpiration < -10;
@endphp

@if ($expiresAt && $daysUntilExpiration <= 20)
    <div class="card mt-3 border-0 shadow-sm">
        <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center">
                <span
                    class="avatar avatar-md fs-4 {{ $daysUntilExpiration < 0 ? 'text-danger' : 'text-warning' }}  me-3">
                    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                </span>

                <div>
                    <p class="mb-0 text-dark">
                        @if ($daysUntilExpiration < 0)
                            هزینه هاست، سرور و پشتیبانی شما در تاریخ {{ verta($expiresAt)->format('Y/m/d') }} منقضی شده
                            است.
                        @elseif ($daysUntilExpiration === 0)
                            هزینه هاست، سرور و پشتیبانی شما امروز منقضی می‌شود.
                        @else
                            هزینه هاست، سرور و پشتیبانی شما تا {{ $daysUntilExpiration }} روز دیگر، در تاریخ
                            {{ verta($expiresAt)->format('Y/m/d') }} منقضی می‌شود.
                        @endif
                    </p>
                    <small class="text-gray">
                        @if ($accessExpired)
                            به دلیل گذشت بیش از 10 روز از تاریخ انقضا، دسترسی به بخش‌های سیستم تا زمان تمدید محدود شده است.
                        @endif
                    </small>
                </div>
            </div>

            <a href="{{ route('admin.tenant-renew') }}"
                class="btn {{ $daysUntilExpiration < 0 ? 'btn-danger' : 'btn-warning' }} text-nowrap">
                تمدید
            </a>
        </div>
    </div>
@endif
