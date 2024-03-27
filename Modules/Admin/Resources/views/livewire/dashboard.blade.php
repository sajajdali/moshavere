<div>

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
    <div class="row">
        <div class="col-md-6">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <div class="card-header d-flex justify-content-between py-0">
                        <span>نوبت های امروز</span>
                        <a href="{{ route('admin.appointment_user.list') }}">مشاهده همه</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive mb-3">
                        <table class="table text-nowrap text-md-nowrap text-center" wire:loading.class="op-0-3">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">نام</th>
                                    <th scope="col">وضعیت</th>
                                    <th scope="col">ساعت</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-center table-success">
                                    <td>5124</td>
                                    <td>زانیار اسلانی</td>
                                    <td>
                                        <span class="badge bg-success">تایید شده</span>
                                    </td>
                                    <td> 10:20 14/12 </td>

                                </tr>
                                <tr class="text-center table-danger">
                                    <td>5125</td>
                                    <td>زهره محمدی</td>
                                    <td>
                                        <span class="badge bg-danger">کنسل شده</span>
                                    </td>
                                    <td> 10:50 14/12 </td>

                                </tr>
                                <tr class="text-center table-success">
                                    <td>5126</td>
                                    <td>محمدی</td>
                                    <td>
                                        <span class="badge bg-success">تایید شده</span>
                                    </td>
                                    <td> 10:20 14/12 </td>

                                </tr>
                                <tr class="text-center table-success">
                                    <td>5127</td>
                                    <td>اقایی</td>
                                    <td>
                                        <span class="badge bg-success">تایید شده</span>
                                    </td>
                                    <td> 10:20 14/12 </td>

                                </tr>
                                {{-- TODO::dynamic alert message --}}
                                {{-- <tr>
                                    <td colspan="100%" class="text-center">
                                        <div class="alert alert-info">
                                            هیچ موردی یافت نشد
                                        </div>
                                    </td>
                                </tr> --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">گزارش نوبت ها</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="chartLine" class="h-275"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@section('scripts')
    {{-- @dd($chartData['mount']) --}}
    <!-- CHARTJS JS -->
    <script src="{{ asset('assets/admin/plugins/chart/Chart.bundle.js') }}"></script>
    <script src="{{ asset('assets/admin/plugins/chart/utils.js') }}"></script>
    {{-- <script src="{{ asset('assets/admin/js/chart.js') }}"></script> --}}
    <script>
        $(document).ready(function() {
            // LIne-Chart
            var ctx = document.getElementById("chartLine").getContext('2d');
            var myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ["فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد"],
                    datasets: [{
                        label: 'موفق',
                        data: [10, 110, 50, 60, 70, 10, 100],
                        backgroundColor: 'transparent',
                        borderColor: '#77bc21',
                        borderWidth: 1,
                        pointBackgroundColor: '#ffffff',
                        pointRadius: 3
                    }, {
                        label: 'کنسل شده',
                        data: [20, 40, 2, 50, 10, 30, 0],
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
        });
    </script>
@endsection
