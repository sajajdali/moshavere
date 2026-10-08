<div class="fin-page">
    @include('finance::components.assets')
    <div class="page-header">
        <div>
            <h1 class="page-title">داشبورد مالی</h1>
        </div>
        <div class="ms-auto pageheader-btn d-flex gap-2 flex-wrap">
            @if ($canCreate)
                <button type="button" class="btn btn-primary" wire:click="$dispatch('finance-open-form')">
                    <i class="fa fa-plus me-1" aria-hidden="true"></i> ثبت پرداخت دستی
                </button>
            @endif
            <a href="{{ route('admin.finance.payments') }}" class="btn btn-secondary"><i class="fa fa-list me-1" aria-hidden="true"></i> همه پرداخت ها</a>
            <a href="{{ route('admin.finance.patients') }}" class="btn btn-secondary"><i class="fa fa-users me-1" aria-hidden="true"></i> گزارش بیماران</a>
        </div>
    </div>
    @include('admin::layouts.components.alert')

    @include('finance::components.filter-bar', ['show' => ['source', 'doctor'], 'options' => $options])

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
            <div class="fin-card-head"><h3 class="fin-card-title"><i class="fa fa-bar-chart"></i> دریافتی روزانه</h3></div>
            <div class="fin-card-body">
                @if ($summary['byDay']->isEmpty())
                    <div class="fin-empty" style="padding: 20px 0">در این بازه پرداختی ثبت نشده است</div>
                @else
                    <div class="fin-days" dir="ltr">
                        @foreach ($summary['byDay'] as $day)
                            <div class="fin-day" title="{{ verta($day->day)->format('Y/m/d') }} : {{ number_format($day->total) }} تومان ({{ $day->count }} پرداخت)">
                                <div class="fin-day-bar" style="height: {{ max(2, round(max(0, $day->total) / $maxDay * 100)) }}%"></div>
                                <span class="fin-day-label">{{ verta($day->day)->format('m/d') }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="fin-card">
                    <div class="fin-card-head"><h3 class="fin-card-title"><i class="fa fa-user-md"></i> به تفکیک پزشک</h3></div>
                    <div class="fin-card-body">@include('finance::components.bars', ['items' => $summary['byDoctor'], 'color' => 'amber'])</div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="fin-card">
                    <div class="fin-card-head">
                        <h3 class="fin-card-title"><i class="fa fa-trophy"></i> بیشترین پرداخت</h3>
                        <a href="{{ route('admin.finance.patients') }}" class="small">همه بیماران</a>
                    </div>
                    <div class="fin-card-body p-0">
                        @if ($summary['topPatients']->isEmpty())
                            <div class="fin-empty" style="padding: 20px 0">داده ای وجود ندارد</div>
                        @else
                            <table class="table table-hover fin-table mb-0">
                                <thead><tr><th>بیمار</th><th class="text-center">تعداد</th><th class="text-end">خالص (تومان)</th></tr></thead>
                                <tbody>
                                    @foreach ($summary['topPatients'] as $patient)
                                        <tr>
                                            <td><a class="fin-link" href="{{ route('admin.finance.patient', $patient->user_id) }}">{{ $patient->name }}</a></td>
                                            <td class="text-center">{{ $patient->count }}</td>
                                            <td class="text-end fin-amount">{{ number_format($patient->total) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($canCreate)
        @livewire(\Modules\Finance\Livewire\PaymentForm::class)
    @endif
</div>
