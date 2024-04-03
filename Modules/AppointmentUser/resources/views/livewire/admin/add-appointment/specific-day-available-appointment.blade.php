<div>
    <div class="page-header">
        <div>
            <h1 class="page-title"> افزودن نوبت برای دکتر <span
                    class="text-danger">{{ $fetchData['doc']->fullName }}</span> </h1>
        </div>
        <button id="changeDocButton" class="btn btn-primary mt-3 mt-sm-0" type="button" class="btn btn-primary"
            data-bs-toggle="modal" data-bs-target="#changeDocmodal">
            تغییر پزشک و بخش</button>
    </div>
    @include('admin::layouts.components.alert')

    <div class="row row-sm">
        <div class="col-md-12">
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between border-bottom">
                    <div>
                        <button class="btn btn-light" wire:click='previousDay' data-bs-toggle="tooltip"
                            data-bs-placement="top" title="روز قبل">
                            <i class="fa fa-arrow-right" aria-hidden="true"></i>
                        </button>
                        <input class="text-center" type="text" id="currentDate"
                            value="{{ $fetchData['selectedDate'] }}" style="max-width: fit-content">
                        <button class="btn btn-light" wire:click='nextDay'>
                            <i class="fa fa-arrow-left" aria-hidden="true" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="روز بعد"></i>
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
                                @if (!empty($this->ShowListOfAppointmentForSpecificDay()))
                                    @foreach ($this->ShowListOfAppointmentForSpecificDay() as $key => $eachTime)
                                        @if ($loop->first)
                                            <tr>
                                                <td colspan="6">
                                                    <div class="alert alert-avatar alert-primary alert-dismissible">
                                                        حضور از ساعت {{ substr($eachTime['from'], 0, -6) }} عصر به بعد
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                        @if ($eachTime['status'] == true)
                                            <tr>
                                                <td class="alert text-center bg-info ">
                                                    {{ $key + 1 }}</td>
                                                <td>
                                                    {{ substr($eachTime['from'], 0, -3) }} -
                                                    {{ substr($eachTime['until'], 0, -3) }}
                                                </td>
                                                <td colspan="4" class="text-center">
                                                    <div class="d-flex align-items-center">
                                                        @if ($edited['status'])
                                                            <button type="button"  style="width: fit-content"
                                                                wire:click='changeAppointmentDate("{{ $eachTime['from'] }}","{{ $eachTime['until'] }}")'
                                                                class="btn btn-sm btn-secondary btn-block"> تغییر ساعت
                                                                نوبت به این ساعت</button>
                                                            @if ($eachTime['gap'])
                                                                <span class="text-danger ms-5">زمان نوبت کمتر از زمان
                                                                    ویزیت
                                                                    میباشد!</span>
                                                            @endif
                                                        @else
                                                            <button type="button" style="width: 124px"
                                                                data-time-start="10:30" data-bs-toggle="modal"
                                                                data-bs-target="#RegistrAnAppointment"
                                                                data-time-end="10:45"
                                                                wire:click='passTimeToRegisterAppointmentModal("{{ $eachTime['from'] }}","{{ $eachTime['until'] }}")'
                                                                class="btn btn-sm btn-success btn-block">ثبت
                                                                نوبت</button>
                                                            @if ($eachTime['gap'])
                                                                <span class="text-danger ms-5">زمان نوبت کمتر از زمان
                                                                    ویزیت
                                                                    میباشد!</span>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @elseif(isset($eachTime['appointment_user_id']))
                                            @php
                                                $ap = Modules\AppointmentUser\app\Models\AppointmentUser::find(
                                                    $eachTime['appointment_user_id'],
                                                );
                                                $user = $ap->user;
                                            @endphp
                                            <tr
                                                class=" @if ($ap->type == Modules\AppointmentUser\Enum\AppointmentUserTypeEnum::BETWEEN_PATIENTS) table-info @else {{ $ap->status->getColor() }} @endif text-center">
                                                <td class="alert text-center bg-info ">{{ $key + 1 }}</td>
                                                <td>
                                                    {{ substr($eachTime['from'], 0, -3) }} -
                                                    {{ substr($eachTime['until'], 0, -3) }}
                                                </td>
                                                <td>
                                                    {{ $user->fullName }}
                                                </td>
                                                <td>
                                                    {{ $user->mobile }}
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge {{ $ap->status->getBadgeColor() }} rounded-pill">
                                                        {{ $ap->status->getName() }}
                                                    </span>
                                                    @if ($ap->type == Modules\AppointmentUser\Enum\AppointmentUserTypeEnum::BETWEEN_PATIENTS)
                                                        <span class="badge bg-info rounded-pill ms-1">
                                                            <strong> {{ $ap->type->getName() }}</strong>
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-danger dropdown-toggle" type="button"
                                                            id="dropdownMenuButton1" data-bs-toggle="dropdown"
                                                            aria-expanded="false">
                                                            عملیات
                                                        </button>
                                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                            <li>
                                                                @can('update', $ap)
                                                                    <a class="dropdown-item"
                                                                        wire:click='editAppointment("{{ $ap->id }}")'
                                                                        href="#">ویرایش</a>
                                                                @endcan
                                                            </li>
                                                            <li>
                                                                @can('delete', $ap)
                                                                    <a class=" dropdown-item delete_confirm_alert"
                                                                        data-label="نوبت" data-id="{{ $ap->id }}"
                                                                        href="#">حذف</a>
                                                                </li>
                                                            @endcan
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                        @if ($loop->last)
                                            <tr>
                                                <td colspan="6">
                                                    <div class="alert alert-avatar alert-primary alert-dismissible">
                                                        حضور تا ساعت {{ substr($eachTime['until'], 0, -6) }} عصر
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @else
                                    <div class="alert alert-avatar alert-warning alert-dismissible">
                                        زمان حضور برای این تاریخ تعیین نشده است!!
                                    </div>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <livewire:appointmentuser::admin.add-appointment.modal.service-and-doctor-modal />
    <livewire:appointmentuser::admin.add-appointment.modal.specific-day-appointment-registration-modal :appId="$fetchData['appId']"
        :appTime="$fetchData['time']" :appDate="verta($fetchData['selectedDate'])->format('Y-m-d')" />
</div>
@push('scripts')
    <!-- SELECT2 JS -->
    <script src="{{ admin_asset('plugins/select2/select2.full.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/admin.sweetalert.js') }}"></script>
    <script>
        $(document).ready(function() {
            var setAppModal = document.querySelector('#RegistrAnAppointment');
            var setAppModalInst = bootstrap.Modal.getOrCreateInstance(setAppModal);
            var myModalEl = document.querySelector('#changeDocmodal');
            var modal = bootstrap.Modal.getOrCreateInstance(myModalEl);

            function addJs() {
                $('#currentDate').persianDatepicker({
                    format: 'L',
                    autoClose: true,
                    onSelect: function(unix) {
                        @this.set('form.changeDate', $('#currentDate').val());
                    }
                });
            };
            addJs();
            Livewire.on('loadJs', function() {
                setTimeout(() => {
                    addJs();
                }, 500);
            });
            @if ($fetchData['showRegisterModal'])

                setAppModalInst.show();
            @endif
            Livewire.on('closeModal', function() {
                modal.hide();
                setAppModalInst.hide();
            });
            Livewire.on('dateHasBeenChange', function(newDate) {
                var currentUrl = window.location.href;
                var baseUrl = currentUrl.split('/').slice(0, -1).join('/');
                var newUrl = baseUrl + '/' + newDate.newDate;
                window.history.pushState({
                    path: newUrl
                }, '', newUrl);
            })
        });
    </script>
@endpush
