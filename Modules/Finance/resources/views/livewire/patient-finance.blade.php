<div class="fin-page">
    @include('finance::components.assets')
    @php
        $name = trim((string) $user->fullName) !== '' ? $user->fullName : $user->mobile;
    @endphp
    @if ($embedded)
        {{-- inside the patient file the patient is already known: only the actions and the all-time total --}}
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            @if ($canCreate)
                <button type="button" class="btn btn-primary" wire:click="$dispatch('finance-open-form', { userId: {{ $user->id }} })">
                    <i class="fa fa-plus me-1" aria-hidden="true"></i> ثبت پرداخت
                </button>
            @endif
            @if ($canExport)
                <button type="button" class="btn btn-success" wire:click="export" wire:loading.attr="disabled" wire:target="export">
                    <i class="fa fa-file-excel-o me-1" aria-hidden="true"></i> خروجی اکسل
                </button>
            @endif
            <span class="ms-auto text-muted small">مجموع کل زمان ها: <strong class="text-body">{{ number_format($allTime->net) }}</strong> تومان
                ({{ number_format($allTime->rows) }} پرداخت)</span>
        </div>
    @else
        <div class="page-header">
            <div>
                <h1 class="page-title">وضعیت مالی بیمار <strong class="text-primary">{{ $name }}</strong></h1>
            </div>
            <div class="ms-auto pageheader-btn d-flex gap-2 flex-wrap">
                @if ($canCreate)
                    <button type="button" class="btn btn-primary" wire:click="$dispatch('finance-open-form', { userId: {{ $user->id }} })">
                        <i class="fa fa-plus me-1" aria-hidden="true"></i> ثبت پرداخت برای این بیمار
                    </button>
                @endif
                @if ($canExport)
                    <button type="button" class="btn btn-success" wire:click="export" wire:loading.attr="disabled" wire:target="export">
                        <i class="fa fa-file-excel-o me-1" aria-hidden="true"></i> خروجی اکسل
                    </button>
                @endif
                <a href="{{ route('admin.user.document', $user) }}" class="btn btn-secondary"><i class="fa fa-folder-open-o me-1" aria-hidden="true"></i> پرونده بیمار</a>
            </div>
        </div>
        @include('admin::layouts.components.alert')

        <div class="fin-card">
            <div class="fin-card-body d-flex flex-wrap gap-4 align-items-center">
                <span><i class="fa fa-mobile text-muted me-1"></i> <strong dir="ltr">{{ $user->mobile }}</strong></span>
                @if (filled($user->national_code))
                    <span><i class="fa fa-id-card-o text-muted me-1"></i> کد ملی: <strong dir="ltr">{{ $user->national_code }}</strong></span>
                @endif
                <span><i class="fa fa-calendar-check-o text-muted me-1"></i> {{ number_format($appointmentsCount) }} نوبت</span>
                <span class="ms-auto text-muted small">مجموع کل زمان ها: <strong class="text-body">{{ number_format($allTime->net) }}</strong> تومان
                    ({{ number_format($allTime->rows) }} پرداخت)</span>
            </div>
        </div>
    @endif

    @include('finance::components.filter-bar', ['show' => ['source', 'type', 'method', 'purpose'], 'options' => $options])

    <div wire:loading.class="op-0-3">
        @include('finance::components.kpis', ['totals' => $summary['totals']])

        <div class="row">
            <div class="col-lg-6">
                <div class="fin-card">
                    <div class="fin-card-head"><h3 class="fin-card-title"><i class="fa fa-credit-card"></i> به تفکیک روش پرداخت</h3></div>
                    <div class="fin-card-body">@include('finance::components.bars', ['items' => $summary['byMethod'], 'color' => ''])</div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="fin-card">
                    <div class="fin-card-head"><h3 class="fin-card-title"><i class="fa fa-tags"></i> به تفکیک دلیل پرداخت</h3></div>
                    <div class="fin-card-body">@include('finance::components.bars', ['items' => $summary['byPurpose'], 'color' => 'green'])</div>
                </div>
            </div>
        </div>

        <div class="fin-card">
            <div class="fin-card-head">
                <h3 class="fin-card-title"><i class="fa fa-list"></i> ریز پرداخت ها</h3>
                <span class="text-muted small">{{ number_format($payments->total()) }} مورد</span>
            </div>
            <div class="fin-card-body">
                @include('finance::components.payment-table', ['payments' => $payments, 'showPatient' => false])
            </div>
        </div>
    </div>

    @if ($canCreate || $canEdit)
        @livewire(\Modules\Finance\Livewire\PaymentForm::class)
    @endif
</div>
