<div wire:init="loadDashboardData">

    <div class="page-header">
        <div>
            <h1 class="page-title">داشبورد مدیریت</h1>
        </div>

        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">خانه</a></li>
                <li class="breadcrumb-item active" aria-current="page">داشبورد</li>
            </ol>
        </div>
    </div>
    @include('admin::layouts.components.alert')
    @unless($dashboardDataLoaded)
    <div class="dashboard-local-loading">
        <div class="dashboard-loading-card">
            <div class="spinner-border text-primary" role="status"></div>
            <span>در حال دریافت اطلاعات داشبورد...</span>
        </div>
    </div>
    @else
    <div class="dashboard-loaded-content">
    <div class="row dashboard-stat-row">
        @if (! Gate::check('admin.dashboard.appointments') &&! Gate::check('admin.dashboard.payment') )
            <div class="col-12 mb-2">
                <div class="alert alert-avatar alert-default alert-dismissible">
                    <i class="fa fa-bell-o me-2" aria-hidden="true"></i>
                    لطفا از طریق منو ، قسمت مورد نظر خود را انتخاب کنید!
                </div>
            </div>
        @endif
        @can('admin.dashboard.appointments')
            <div class="col-lg-12 col-sm-12 col-md-12 col-xl-6 mb-4 d-flex">
                <div class="card dashboard-stat-card dashboard-stat-primary overflow-hidden border-0 shadow-sm h-100 w-100">
                    <div class="card-body">
                        <div class="row align-items-center h-100 g-3 dashboard-today-card-row">
                            <div class="col-xl-4 col-lg-4 col-md-12">
                                <p class="text-muted fs-13 mb-2">نوبت های امروز</p>
                                <h2 class="dashboard-stat-value mb-0 fw-semibold">{{ number_format((float) data_get($fetchData, 'today_appointment', 0)) }}</h2>
                                <small class="dashboard-stat-meta d-block mt-3 text-primary">نمای سریع وضعیت امروز</small>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-12">
                                <div class="dashboard-mini-grid">
                                    <div>
                                        <span>۷ روز آینده</span>
                                        <strong>{{ number_format((float) data_get($fetchData, 'appointment_insights.next_7_days', 0)) }}</strong>
                                    </div>
                                    <div>
                                        <span>منتظر پرداخت</span>
                                        <strong>{{ number_format((float) data_get($fetchData, 'appointment_insights.wait_payment', 0)) }}</strong>
                                    </div>
                                    <div>
                                        <span>لغو امروز</span>
                                        <strong>{{ number_format((float) data_get($fetchData, 'appointment_insights.today_cancel', 0)) }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 d-none d-lg-flex top-icn dash justify-content-end">
                                <div class="counter-icon bg-primary-transparent dash ms-auto">
                                    <i class="fa fa-calendar-check-o text-primary" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if(auth()->user()->isAdmin())
            @isset($fetchData['shsms'])
            <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3 mb-4 d-flex">
                <div class="card dashboard-stat-card dashboard-stat-secondary overflow-hidden border-0 shadow-sm h-100 w-100">
                    <div class="card-body">
                        <div class="row align-items-center h-100">
                            <div class="col">
                                <p class="text-muted fs-13 mb-2">اعتبار پیامک</p>
                                <div class="d-flex align-items-center">
                                    <h3 class="dashboard-stat-value mb-0 fw-semibold">{{ is_numeric($fetchData['shsms']) ? number_format((float) $fetchData['shsms']) : $fetchData['shsms'] }} </h3>
                                    <small class="me-1">تومان</small>
                                </div>
                                <a href="https://shsms.ir" class="dashboard-stat-meta d-block mt-3 text-secondary">شارژ پنل پیامکی</a>
                            </div>
                            <div class="col col-auto top-icn dash">
                                <div class="counter-icon bg-secondary-transparent dash ms-auto">
                                    <i class="fa fa-commenting-o text-secondary" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endisset
            @endif
        @endcan
        @can('admin.dashboard.payment')
            <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3 mb-4 d-flex">
                <div class="card dashboard-stat-card dashboard-stat-warning overflow-hidden border-0 shadow-sm h-100 w-100">
                    <div class="card-body">
                        <div class="row align-items-center h-100">
                            <div class="col">
                                <p class="text-muted fs-13 mb-2">مجموع پرداختی های امروز</p>
                                <h3 class="dashboard-stat-value mb-0 fw-semibold">{{ number_format((float) data_get($this->fetchData, 'transactiontotal', 0)) }}</h3>
                                <small class="dashboard-stat-meta d-block mt-3 text-warning">تراکنش‌های موفق</small>
                            </div>
                            <div class="col col-auto top-icn dash">
                                <div class="counter-icon bg-warning-transparent dash ms-auto">
                                    <i class="fa fa-money text-warning" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    </div>
    @can('admin.dashboard.appointments')
        <div class="row align-items-stretch">
            <div class="col-md-6 mb-4 d-flex">
                <div class="card dashboard-panel-card overflow-hidden border-0 shadow-sm h-100 w-100">
                    <div class="card-body">
                        <div class="card-header d-flex justify-content-center align-items-center py-1">
                            <div class="d-flex align-items-center gap-2" wire:ignore>
                                <button type="button" class="btn btn-link text-primary shadow-none border-0 fs-20 px-2"
                                    wire:click="selectAdjacentAppointmentDate('previous')" title="نوبت قبلی">
                                    <i class="fa fa-angle-left" aria-hidden="true"></i>
                                </button>
                                <input class="form-control text-center datePicker border-0 shadow-none fw-semibold fs-16"
                                    id="dashboard-appointment-date" data-name="selectedAppointmentDate" data-jdp
                                    wire:model="selectedAppointmentDate" type="text" style="max-width: 155px;">
                                <button type="button" class="btn btn-link text-primary shadow-none border-0 fs-20 px-2"
                                    wire:click="selectAdjacentAppointmentDate('next')" title="نوبت بعدی">
                                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive mb-3">
                            <table class="table text-nowrap text-md-nowrap text-center" wire:loading.class="op-0-3">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">نام</th>
                                        <th scope="col">بخش</th>
                                        <th scope="col">وضعیت</th>
                                        <th scope="col">
                                            <button type="button"
                                                class="btn btn-link p-0 text-decoration-none text-reset fw-semibold"
                                                wire:click="sortAppointmentsByTime">
                                                ساعت
                                                @if ($appointmentSortPriority === 'time')
                                                    <i class="fa {{ $appointmentTimeSortDirection === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }} ms-1 text-primary"
                                                        aria-hidden="true"></i>
                                                @else
                                                    <i class="fa fa-sort ms-1 text-muted" aria-hidden="true"></i>
                                                @endif
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($today_app->isNotEmpty())
                                        @foreach ($today_app as $app)
                                            <tr class="text-center table-success">
                                                <td>{{ $app->id }}</td>
                                                <td>{{ $app->user->fullName }}</td>
                                                <td>{{ $app->service?->title ?? '-' }}</td>
                                                <td>
                                                    <span
                                                        class="badge {{ $app->status->getBadgeColor() }}">{{ $app->status->getName() }}</span>
                                                </td>
                                                <td>{{ verta($app->date_visit)->format('H:i') }}</td>

                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="100%" class="text-center">
                                                <div class="alert alert-info">
                                                    نوبتی برای تاریخ انتخابی یافت نشد
                                                    <a class="btn btn-success ms-2"
                                                        href="{{ route('admin.appointment_user.addApp') }}">ثبت نوبت</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-center">
                                {{ $today_app->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4 d-flex" wire:ignore>
                <div class="card dashboard-panel-card border-0 shadow-sm h-100 w-100">
                    <div class="card-header border-bottom">
                        <h3 class="card-title">گزارش نوبت ها</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 275px;">
                            <canvas id="chartLine"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if (!empty($fetchData['incoming_call_status']))
            <div class="row">
                <div class="col-12 mb-4" wire:ignore>
                    <div class="card dashboard-panel-card overflow-hidden border-0 shadow-sm">
                        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                            <h3 class="card-title">تماس‌های ورودی</h3>
                            <a href="{{ route('admin.incoming-calls') }}">جزئیات</a>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-4">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <h3 class="mb-1 fw-semibold">{{ data_get($fetchData, 'incoming_calls.today', 0) }}</h3>
                                            <p class="text-muted fs-12 mb-0">امروز</p>
                                        </div>
                                        <div class="col-4 dashboard-metric-divider">
                                            <h3 class="mb-1 fw-semibold">{{ data_get($fetchData, 'incoming_calls.total', 0) }}</h3>
                                            <p class="text-muted fs-12 mb-0">کل تماس‌ها</p>
                                        </div>
                                        <div class="col-4">
                                            <h3 class="mb-1 fw-semibold fs-16">{{ data_get($fetchData, 'incoming_calls.latest') ?? '-' }}</h3>
                                            <p class="text-muted fs-12 mb-0">آخرین شماره</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <div class="chart-container" style="height: 190px;">
                                        <canvas id="incomingCallsChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="row align-items-stretch">
            <div class="col-xl-8 col-lg-7 col-md-12 mb-4 d-flex">
                <div class="card dashboard-panel-card overflow-hidden border-0 shadow-sm h-100 w-100">
                    <div class="card-header border-bottom">
                        <h3 class="card-title">نمای کلی نوبت‌ها</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="p-4 bg-primary br-7 text-white h-100">
                                    <p class="mb-1 opacity-75">نوبت‌های ۷ روز آینده</p>
                                    <div class="d-flex align-items-end justify-content-between">
                                        <h2 class="mb-0 fw-semibold">{{ data_get($fetchData, 'appointment_insights.next_7_days', 0) }}</h2>
                                        <i class="fa fa-calendar-plus-o fs-30 opacity-75" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="p-3 bg-info-transparent br-7">
                                            <h3 class="mb-1 fw-semibold">{{ data_get($fetchData, 'appointment_insights.wait_payment', 0) }}</h3>
                                            <p class="text-muted fs-12 mb-0">منتظر پرداخت</p>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 bg-danger-transparent br-7">
                                            <h3 class="mb-1 fw-semibold">{{ data_get($fetchData, 'appointment_insights.today_cancel', 0) }}</h3>
                                            <p class="text-muted fs-12 mb-0">لغو امروز</p>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="p-3 bg-success-transparent br-7">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                            <h3 class="mb-1 fw-semibold">{{ number_format((float) data_get($fetchData, 'appointment_insights.month_revenue', 0)) }}</h3>
                                                    <p class="text-muted fs-12 mb-0">درآمد ماه</p>
                                                </div>
                                                <i class="fa fa-credit-card fs-24 text-success" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="chart-container" style="height: 275px;" wire:ignore>
                            <canvas id="appointmentsWeekChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-5 col-md-12 mb-4 d-flex" wire:ignore>
                <div class="card dashboard-panel-card overflow-hidden border-0 shadow-sm h-100 w-100">
                    <div class="card-header border-bottom">
                        <h3 class="card-title">وضعیت نوبت‌ها</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 350px;">
                            <canvas id="appointmentStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row align-items-stretch">
            <div class="{{ Gate::check('admin.dashboard.payment') ? 'col-xl-7' : 'col-12' }} col-md-12 mb-4 d-flex">
                <div class="card dashboard-panel-card overflow-hidden border-0 shadow-sm h-100 w-100">
                    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                        <h3 class="card-title">آخرین نوبت‌ها</h3>
                        <a href="{{ route('admin.appointment_user.list') }}">همه نوبت‌ها</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-nowrap text-md-nowrap text-center mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>بیمار</th>
                                        <th>پزشک</th>
                                        <th>تاریخ</th>
                                        <th>وضعیت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse (data_get($fetchData, 'recent_appointments', collect()) as $appointment)
                                        <tr>
                                            <td>{{ $appointment->id }}</td>
                                            <td>{{ $appointment->user?->fullName ?? '-' }}</td>
                                            <td>{{ $appointment->doctor?->fullName ?? '-' }}</td>
                                            <td>{{ verta($appointment->date_visit)->format('Y/m/d H:i') }}</td>
                                            <td><span class="badge {{ $appointment->status->getBadgeColor() }}">{{ $appointment->status->getName() }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5"><div class="alert alert-info mb-0">نوبتی یافت نشد.</div></td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @can('admin.dashboard.payment')
                <div class="col-xl-5 col-md-12 mb-4 d-flex">
                    <div class="card dashboard-panel-card overflow-hidden border-0 shadow-sm h-100 w-100">
                        <div class="card-header border-bottom">
                            <h3 class="card-title">آخرین پرداخت‌ها</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table text-nowrap text-md-nowrap text-center mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>کاربر</th>
                                            <th>مبلغ</th>
                                            <th>وضعیت</th>
                                            <th>زمان</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse (data_get($fetchData, 'recent_transactions', collect()) as $transaction)
                                            <tr>
                                                <td>{{ $transaction->id }}</td>
                                                <td>{{ $transaction->user?->fullName ?? '-' }}</td>
                                                <td>{{ number_format((float) $transaction->total_cost) }}</td>
                                                <td><span class="badge {{ $transaction->status->badgeClass() }}">{{ $transaction->status->getName() }}</span></td>
                                                <td>{{ verta($transaction->created_at)->format('Y/m/d H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5"><div class="alert alert-info mb-0">پرداختی یافت نشد.</div></td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan
        </div>
        <div class="row">
            @can('user.approveDoc')
            @if (isset($fetchData['SelfRegistrationDoctors']) && $fetchData['SelfRegistrationDoctors']->isNotEmpty())
            <div class="col-12 mb-4">
                <div class="card dashboard-panel-card overflow-hidden border-0 shadow-sm">
                    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                        <h3 class="card-title">درخواست ثبت نام پزشک</h3>
                        <a href="{{ route('admin.user.index') }}">مشاهده همه</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive mb-3">
                            <table class="table text-nowrap text-md-nowrap text-center" wire:loading.class="op-0-3">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">نام</th>
                                        <th scope="col">ساعت</th>
                                        <th scope="col">مدیریت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                        @foreach ($fetchData['SelfRegistrationDoctors'] as $docRequest)
                                            <tr class="text-center table-info">
                                                <td>{{ $docRequest->id }}</td>
                                                <td>{{ $docRequest->full_name }}</td>
                                                <td>{{ verta($docRequest->created_at)->format('H:i') }}</td>
                                                <td> <a class="btn btn-success  text-white " href="{{route('admin.doctor.info',['user'=>$docRequest->id]) }}">پروفایل پزشک</a> </td>
                                            </tr>
                                        @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @endcan
        </div>
    @endcan
    </div>
    @endunless

</div>
@push('styles')
    <style>
        .dashboard-local-loading {
            min-height: 360px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dashboard-loading-card {
            min-width: 280px;
            padding: 28px;
            border: 1px solid #e9edf4;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 4px 18px rgba(31, 45, 61, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            color: #4b5563;
            font-weight: 600;
        }

        .dashboard-stat-card {
            --dashboard-accent: #6259ca;
            position: relative;
            min-height: 154px;
            border-top: 3px solid var(--dashboard-accent) !important;
            transition: transform 180ms ease, box-shadow 180ms ease;
        }

        .dashboard-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(31, 45, 61, 0.1) !important;
        }

        .dashboard-stat-card .card-body {
            padding: 1.35rem;
        }

        .dashboard-stat-primary { --dashboard-accent: #6259ca; }
        .dashboard-stat-secondary { --dashboard-accent: #6c757d; }
        .dashboard-stat-info { --dashboard-accent: #45aaf2; }
        .dashboard-stat-warning { --dashboard-accent: #f7b731; }

        .dashboard-stat-value {
            min-height: 34px;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        .dashboard-stat-meta {
            min-height: 18px;
            font-size: 12px;
        }

        .dashboard-mini-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .dashboard-mini-grid > div {
            padding: 10px;
            border-radius: 8px;
            background: #f4f7ff;
            border: 1px solid #e1e8ff;
            text-align: center;
        }

        .dashboard-mini-grid span {
            display: block;
            color: #6b7280;
            font-size: 11.5px;
            margin-bottom: 5px;
            white-space: normal;
            overflow-wrap: anywhere;
            line-height: 1.6;
        }

        .dashboard-mini-grid strong {
            display: block;
            color: #3742a0;
            font-size: 18px;
            line-height: 1.2;
        }

        .dashboard-panel-card > .card-header {
            min-height: 56px;
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
        }

        .dashboard-panel-card .card-title {
            margin-bottom: 0;
        }

        .dashboard-metric-divider {
            border-right: 1px solid #e9edf4;
            border-left: 1px solid #e9edf4;
        }

        @media (max-width: 991.98px) {
            .dashboard-metric-divider {
                border-right: 1px solid #e9edf4;
                border-left: 1px solid #e9edf4;
            }

            .dashboard-mini-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .dashboard-mini-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush
@section('scripts')
    {{-- @dd($chartData['mount']) --}}
    <!-- CHARTJS JS -->
    <script src="{{ asset('assets/admin/plugins/chart/Chart.bundle.js') }}"></script>
    <script src="{{ asset('assets/admin/plugins/chart/utils.js') }}"></script>
    {{-- <script src="{{ asset('assets/admin/js/chart.js') }}"></script> --}}
    <script>
        $(document).ready(function() {
            jalaliDatepicker.startWatch();
            $(document).on('input', '#dashboard-appointment-date[data-jdp]', function() {
                @this.set($(this).data('name'), $(this).val());
            });

            Livewire.on('dashboardAppointmentDateChanged', function(event) {
                $('#dashboard-appointment-date').val(event.date);
            });

            var dashboardCharts = {
                month: {!! json_encode(data_get($fetchData, 'chart.month', []), 256) !!},
                successful: {!! json_encode(data_get($fetchData, 'chart.data.successful', [])) !!},
                canceled: {!! json_encode(data_get($fetchData, 'chart.data.canceld', [])) !!},
                incoming_labels: {!! json_encode(collect(data_get($fetchData, 'incoming_calls_chart', []))->pluck('label')->values(), 256) !!},
                incoming_counts: {!! json_encode(collect(data_get($fetchData, 'incoming_calls_chart', []))->pluck('count')->values()) !!},
                week_labels: {!! json_encode(collect(data_get($fetchData, 'appointments_week_chart', []))->pluck('label')->values(), 256) !!},
                week_counts: {!! json_encode(collect(data_get($fetchData, 'appointments_week_chart', []))->pluck('count')->values()) !!},
                status_labels: {!! json_encode(collect(data_get($fetchData, 'appointment_status_chart', []))->pluck('label')->values(), 256) !!},
                status_counts: {!! json_encode(collect(data_get($fetchData, 'appointment_status_chart', []))->pluck('count')->values()) !!},
            };

            function initDashboardCharts(charts) {
            charts = charts || dashboardCharts;
            var lineCanvas = document.getElementById("chartLine");

            // LIne-Chart
            if (lineCanvas) {
                new Chart(lineCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: charts.month || [],
                        datasets: [{
                            label: 'موفق',
                            data: charts.successful || [],
                            backgroundColor: 'transparent',
                            borderColor: '#77bc21',
                            borderWidth: 1,
                            pointBackgroundColor: '#ffffff',
                            pointRadius: 3
                        }, {
                            label: 'کنسل شده',
                            data: charts.canceled || [],
                            backgroundColor: 'transparent',
                            borderColor: '#e984b1',
                            borderWidth: 1,
                            pointBackgroundColor: '#ffffff',
                            pointRadius: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            labels: {
                                fontColor: "#77778e"
                            }
                        }
                    }
                });
            }

            var incomingCallsCanvas = document.getElementById("incomingCallsChart");
            if (incomingCallsCanvas) {
                new Chart(incomingCallsCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: charts.incoming_labels || [],
                        datasets: [{
                            label: 'تماس ورودی',
                            data: charts.incoming_counts || [],
                            backgroundColor: '#5e2dd8',
                            borderColor: '#5e2dd8',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            display: false
                        },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    precision: 0
                                }
                            }]
                        }
                    }
                });
            }

            var appointmentsWeekCanvas = document.getElementById("appointmentsWeekChart");
            if (appointmentsWeekCanvas) {
                new Chart(appointmentsWeekCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: charts.week_labels || [],
                        datasets: [{
                            label: 'نوبت',
                            data: charts.week_counts || [],
                            backgroundColor: '#0dcd94',
                            borderColor: '#0dcd94',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            display: false
                        },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    precision: 0
                                }
                            }]
                        }
                    }
                });
            }

            var appointmentStatusCanvas = document.getElementById("appointmentStatusChart");
            if (appointmentStatusCanvas) {
                new Chart(appointmentStatusCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: charts.status_labels || [],
                        datasets: [{
                            data: charts.status_counts || [],
                            backgroundColor: ['#0dcd94', '#45aaf2', '#f82649', '#f7b731'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            position: 'bottom',
                            labels: {
                                fontColor: "#77778e"
                            }
                        }
                    }
                });
            }
            }

            initDashboardCharts();
            Livewire.on('dashboardDataLoaded', function() {
                var event = arguments[0] || {};
                dashboardCharts = event.charts || dashboardCharts;
                setTimeout(function() {
                    initDashboardCharts(dashboardCharts);
                }, 100);
            });
        });
    </script>
@endsection
