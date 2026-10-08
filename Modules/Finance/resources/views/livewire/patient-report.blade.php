<div class="fin-page">
    @include('finance::components.assets')
    <div class="page-header">
        <div>
            <h1 class="page-title">گزارش مالی بیماران</h1>
        </div>
        <div class="ms-auto pageheader-btn d-flex gap-2 flex-wrap">
            @if ($canExport)
                <button type="button" class="btn btn-success" wire:click="export" wire:loading.attr="disabled" wire:target="export">
                    <i class="fa fa-file-excel-o me-1" aria-hidden="true"></i> خروجی اکسل
                </button>
            @endif
            <a href="{{ route('admin.finance.dashboard') }}" class="btn btn-secondary"><i class="fa fa-pie-chart me-1" aria-hidden="true"></i> داشبورد مالی</a>
        </div>
    </div>
    @include('admin::layouts.components.alert')

    @include('finance::components.filter-bar', ['show' => ['search', 'source', 'method', 'purpose', 'doctor'], 'options' => $options])
    @include('finance::components.kpis', ['totals' => $totals])

    @php
        $columns = [
            'payments_count' => 'تعداد پرداخت',
            'system_total' => 'پرداخت سیستمی',
            'manual_total' => 'پرداخت دستی',
            'refund_total' => 'بازگشت وجه',
            'net' => 'خالص پرداخت',
            'last_paid_at' => 'آخرین پرداخت',
        ];
    @endphp
    <div class="fin-card" wire:loading.class="op-0-3" wire:target="filter,setRange,resetFilters,sortBy,gotoPage,nextPage,previousPage">
        <div class="fin-card-head">
            <h3 class="fin-card-title"><i class="fa fa-users"></i> بیماران</h3>
            <span class="text-muted small">{{ number_format($patients->total()) }} بیمار</span>
        </div>
        <div class="fin-card-body">
            @if ($patients->isEmpty())
                <div class="fin-empty"><i class="fa fa-users" aria-hidden="true"></i>بیماری با این فیلترها پیدا نشد</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover fin-table text-nowrap text-center mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th class="text-start">بیمار</th>
                                @foreach ($columns as $key => $label)
                                    <th class="fin-th-sort" wire:click="sortBy('{{ $key }}')">
                                        {{ $label }}
                                        @if ($sort === $key)
                                            <i class="fa fa-sort-{{ $direction === 'asc' ? 'asc' : 'desc' }}"></i>
                                        @else
                                            <i class="fa fa-sort text-muted"></i>
                                        @endif
                                    </th>
                                @endforeach
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($patients as $index => $row)
                                <tr wire:key="patient-{{ $row->user_id }}">
                                    <td class="text-muted">{{ $patients->firstItem() + $index }}</td>
                                    <td class="text-start">
                                        <a class="fin-link" href="{{ route('admin.finance.patient', $row->user_id) }}">{{ $row->name }}</a>
                                        @if ($row->mobile)
                                            <span class="fin-sub" dir="ltr">{{ $row->mobile }}</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($row->payments_count) }}</td>
                                    <td>{{ number_format($row->system_total) }}</td>
                                    <td>{{ number_format($row->manual_total) }}</td>
                                    <td class="{{ $row->refund_total > 0 ? 'text-danger' : '' }}">{{ number_format($row->refund_total) }}</td>
                                    <td class="fin-amount">{{ number_format($row->net) }}</td>
                                    <td>
                                        @if ($row->last_paid_at)
                                            {{ verta($row->last_paid_at)->format('Y/m/d') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.finance.patient', $row->user_id) }}">مشاهده</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $patients->links() }}</div>
            @endif
        </div>
    </div>
</div>
