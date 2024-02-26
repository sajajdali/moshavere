<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">شما در حال افزودن نوبت برای دکتر <span class="text-danger">غلامعلی</span> در بخش <span
                    class="text-danger">ستون فقرات</span> هستید </h1>
        </div>
        <button id="changeDocButton" class="btn btn-primary mt-3 mt-sm-0" type="button" class="btn btn-primary"
            data-bs-toggle="modal" data-bs-target="#changeDocmodal">
            تغییر پزشک و بخش</button>
    </div>
    @if (isset($tempMessage))
    <div class="alert alert-success" role="alert">
        <i class="fa fa-check-square fa-xl" aria-hidden="true"></i>
       {{$tempMessage}}
      </div>
    @endif

    <div class="row row-sm">
        <div class="col-md-12">
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between border-bottom">
                    <div>
                        <button class="btn btn-light" wire:click='previousDay'>
                            <i class="fa fa-arrow-right" aria-hidden="true"></i>
                        </button>
                        <input class="text-center" type="text" value="شنبه 1402:01:15" id="currentDate"
                            style="max-width: fit-content">
                        <button class="btn btn-light" wire:click='nextDay'>
                            <i class="fa fa-arrow-left" aria-hidden="true"></i>
                        </button>
                    </div>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#RegistrAnAppointment">ثبت
                        نوبت</button>
                </div>
                <div class="card-body" wire:loading.class="opacity-50">
                    <div class="spinner-border text-primary position-absolute top-50 start-50 " role="status"
                        wire:loading>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered mbn table_appointment" id="appointment_content"
                            style="">
                            <thead>
                                <tr class="table-primary">
                                    <th class="text-center">نوبت</th>
                                    <th class="text-center">ساعت</th>
                                    <th class="text-center">نام و نام خانوادگی</th>
                                    <th class="text-center">موبایل</th>
                                    <th class="text-center">وضعیت</th>
                                    <th class="text-center">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6">
                                        <div class="alert alert-avatar alert-primary alert-dismissible">
                                            حضور از ساعت 6 عصر به بعد
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="alert text-center bg-info ">1</td>
                                    <td>
                                        10:30 - 10:45
                                    </td>
                                    <td colspan="4" class="text-center">
                                        <button type="button" style="width: 124px" data-time-start="10:30"
                                            data-bs-toggle="modal" data-bs-target="#RegistrAnAppointment"
                                            data-time-end="10:45" class="btn btn-sm btn-success btn-block">ثبت
                                            نوبت</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="alert text-center bg-info">2</td>
                                    <td>
                                        10:45 - 11:00
                                    </td>
                                    <td colspan="4" class="text-center">
                                        <button type="button" style="width: 124px" data-time-start="10:45"
                                            data-bs-toggle="modal" data-bs-target="#RegistrAnAppointment"
                                            data-time-end="11:00" class="btn btn-sm  btn-success btn-block">ثبت
                                            نوبت</button>
                                    </td>
                                </tr>
                                <tr class="table-success">
                                    <td class="alert text-center bg-info ">3</td>
                                    <td>
                                        10:45 - 10:50
                                    </td>
                                    <td>
                                        حامد ریسی
                                    </td>
                                    <td>
                                        0937 60 20 827
                                    </td>
                                    <td>
                                        ثبت شده
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-danger dropdown-toggle" type="button"
                                                id="dropdownMenuButton1" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                عملیات
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                <li><a class="dropdown-item" href="#">ویرایش</a></li>
                                                <li><a class="dropdown-item" href="#">حذف</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="table-primary">
                                    <td class="alert text-center bg-info ">3</td>
                                    <td>
                                        10:45 - 10:50
                                    </td>
                                    <td>
                                        پیمان یوسفی
                                    </td>
                                    <td>
                                        0937 60 20 827
                                    </td>
                                    <td>
                                        در انتظار
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-danger dropdown-toggle" type="button"
                                                id="dropdownMenuButton1" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                عملیات
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                <li><a class="dropdown-item" href="#">ویرایش</a></li>
                                                <li><a class="dropdown-item" href="#">حذف</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="table-warning">
                                    <td class="alert text-center bg-info ">3</td>
                                    <td>
                                        10:45 - 10:50
                                    </td>
                                    <td>
                                        صصیصیص صی
                                    </td>
                                    <td>
                                        0937 60 20 827
                                    </td>
                                    <td>
                                        پرداخت نکرده
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-danger dropdown-toggle" type="button"
                                                id="dropdownMenuButton1" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                عملیات
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                <li><a class="dropdown-item" href="#">ویرایش</a></li>
                                                <li><a class="dropdown-item" href="#">حذف</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="table-danger">
                                    <td class="alert text-center bg-info ">3</td>
                                    <td>
                                        10:45 - 10:50
                                    </td>
                                    <td>
                                        حامد ریسی
                                    </td>
                                    <td>
                                        0937 60 20 827
                                    </td>
                                    <td>
                                        لغو شده
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-danger dropdown-toggle" type="button"
                                                id="dropdownMenuButton1" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                عملیات
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                <li><a class="dropdown-item" href="#">ویرایش</a></li>
                                                <li><a class="dropdown-item" href="#">حذف</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6">
                                        <div class="alert alert-avatar alert-primary alert-dismissible">
                                            حضور تا ساعت 10 عصر
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <livewire:appointmentsetting::add-appointment.modal.change-doc-modal />
    <livewire:appointmentsetting::add-appointment.modal.specific-day-appointment-registration-modal />
</div>
@push('scripts')
    <!-- SELECT2 JS -->
    <script src="{{ admin_asset('plugins/select2/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            var myModalEl = document.getElementById('changeDocmodal')
            myModalEl.addEventListener('show.bs.modal', function(event) {
                setTimeout(() => {
                    $('.addSelectJs').select2();
                }, 1000);
            })
            $('#currentDate').persianDatepicker({
                initialValue: false,
                format: 'L',
                autoClose: true,
                onSelect: function(unix) {
                    @this.set('currentDate', $('#currentDate').val());
                }
            });
            Livewire.on('closeModal', function() {
                var myModalEl = document.querySelector('#changeDocmodal')
                var modal = bootstrap.Modal.getOrCreateInstance(myModalEl)
                modal.hide();

                var setAppModal = document.querySelector('#RegistrAnAppointment')
                var setAppModalInst = bootstrap.Modal.getOrCreateInstance(setAppModal)
                setAppModalInst.hide();
            });
        });
    </script>
@endpush
