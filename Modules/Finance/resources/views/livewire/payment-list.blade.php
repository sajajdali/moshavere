<div class="fin-page">
    @include('finance::components.assets')
    <div class="page-header">
        <div>
            <h1 class="page-title">پرداخت ها</h1>
        </div>
        <div class="ms-auto pageheader-btn d-flex gap-2 flex-wrap">
            @if ($canCreate)
                <button type="button" class="btn btn-primary" wire:click="$dispatch('finance-open-form')">
                    <i class="fa fa-plus me-1" aria-hidden="true"></i> ثبت پرداخت دستی
                </button>
            @endif
            @if ($canExport)
                <button type="button" class="btn btn-success" wire:click="export" wire:loading.attr="disabled" wire:target="export">
                    <i class="fa fa-file-excel-o me-1" aria-hidden="true"></i> خروجی اکسل
                </button>
            @endif
            <a href="{{ route('admin.finance.dashboard') }}" class="btn btn-secondary"><i class="fa fa-pie-chart me-1" aria-hidden="true"></i> داشبورد مالی</a>
        </div>
    </div>
    @include('admin::layouts.components.alert')

    @include('finance::components.filter-bar', ['show' => ['search', 'source', 'type', 'method', 'purpose', 'doctor'], 'options' => $options])
    @include('finance::components.kpis', ['totals' => $totals])

    <div class="fin-card" wire:loading.class="op-0-3" wire:target="filter,setRange,resetFilters,gotoPage,nextPage,previousPage">
        <div class="fin-card-head">
            <h3 class="fin-card-title"><i class="fa fa-list"></i> لیست پرداخت ها</h3>
            <span class="text-muted small">{{ number_format($payments->total()) }} مورد</span>
        </div>
        <div class="fin-card-body">
            @include('finance::components.payment-table', ['payments' => $payments, 'showPatient' => true])
        </div>
    </div>

    @if ($canCreate || $canEdit)
        @livewire(\Modules\Finance\Livewire\PaymentForm::class)
    @endif
</div>
