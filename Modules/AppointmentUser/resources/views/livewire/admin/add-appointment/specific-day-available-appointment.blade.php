<div>
    <div class="page-header align-items-center">
        <div>
            @if (!$edited['status'])
                <h1 class="page-title"> افزودن نوبت برای {{ $fetchData['doc']->speciality_type == 1 ? 'دکتر' : '' }}
                    <strong class="text-danger">{{ $fetchData['doc']->fullName }}</strong>
                    در بخش <strong class="text-danger"> {{ $this->fetchData['service']->title ?? '' }}</strong>
                </h1>
            @else
                <h1 class="page-title">تغییر زمان نوبت</h1>
            @endif
        </div>
        @if ($fetchData['showChangeServiceBtn'])
            <button id="changeDocButton" class="btn btn-primary mt-3 mt-sm-0" type="button" class="btn btn-primary"
                data-bs-toggle="modal" data-bs-target="#changeDocmodal">
                تغییر پزشک و بخش</button>
        @endif
    </div>
    @include('admin::layouts.components.alert')
    @include('admin::layouts.components.loading')

    @if ($edited['status'])
        <div class="col-md-12 alert alert-secondary fade show" role="alert">
            شما در حال تغییر زمان نوبت "{{ $edited['old_app']->user->fullName }}" هستید!!
        </div>
    @endif
    <div class="row row-sm">
        <div class="col-md-12">
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between border-bottom">
                    <div>
                        <button type="button" class="btn btn-light" wire:click='previousDay' wire:loading.attr="disabled" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="روز قبل">
                            <i class="fa fa-arrow-right" aria-hidden="true"></i>
                        </button>
                        <input class="text-center" type="text" id="currentDate" data-jdp data-name="form.changeDate"
                            wire:key="appointment-date-{{ $fetchData['selectedDate']->format('Y-m-d') }}"
                            wire:model="form.changeDate"
                            value="{{ $form['changeDate'] }}"
                            style="max-width: fit-content">
                        <button type="button" class="btn btn-light" wire:click='nextDay' wire:loading.attr="disabled">
                            <i class="fa fa-arrow-left" aria-hidden="true" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="روز بعد"></i>
                        </button>
                    </div>
                    <div class="mt-2">
                        <h5><mark class="p-2">
                                ثبت نوبت در روز {{ verta($fetchData['selectedDate'])->format('l d F Y') }}</mark></h5>
                    </div>
                    @if (!$edited['status'])
                        <button class="btn btn-success" data-bs-toggle="modal" wire:click='dateHasBeenChange'
                            data-bs-target="#RegistrAnAppointment">ثبت
                            نوبت</button>
                    @endif
                </div>
                <div class="card-body">
                    @if (!empty($fetchData['navigationMessage']))
                        <div class="alert alert-info" role="status">{{ $fetchData['navigationMessage'] }}</div>
                    @endif
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
                            <tbody wire:key="appointment-times-{{ $fetchData['selectedDate']->format('Y-m-d') }}">
                                @if (!empty($this->ShowListOfAppointmentForSpecificDay()))
                                    @foreach ($this->ShowListOfAppointmentForSpecificDay() as $key => $eachTime)
                                        @if ($loop->first)
                                            <tr>
                                                <td colspan="6">
                                                    <div class="alert alert-avatar alert-primary alert-dismissible">
                                                        حضور از ساعت
                                                        <strong>{{ substr($eachTime['from'], 0, -6) }}</strong> در روز
                                                        <strong>{{ verta($fetchData['selectedDate'])->format('l') }}</strong>
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
                                                            <button type="button" style="width: fit-content"
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
                                                                data-time-start="10:30" data-time-end="10:45"
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
                                                $user = $ap?->user;
                                            @endphp
                                            @if (isset($ap))
                                                <tr class="{{ $ap->getColor() }} text-center">
                                                    <td class="alert text-center bg-info ">{{ $key + 1 }}</td>
                                                    <td>
                                                        {{ substr($eachTime['from'], 0, -3) }} -
                                                        {{ substr($eachTime['until'], 0, -3) }}
                                                    </td>
                                                    <td>
                                                        <span>
                                                            {{ $user->fullName }} -
                                                            @if (
                                                                $ap->setting?->detail[\Modules\AppointmentSetting\app\Models\AppointmentSetting::OPERATORS][
                                                                    \Modules\AppointmentSetting\app\Models\AppointmentSetting::STATUS
                                                                ]
                                                            )
                                                                <span
                                                                    class="badge badge-sm bg-info">{{ $ap->operator?->full_name ?? 'بدون اپراتور' }}
                                                                </span>
                                                            @endif
                                                        </span>
                                                        <small>
                                                            {{ $ap->service->title ?? '(بخش حذف شده)' }}
                                                            @if ($ap->hasSegment())
                                                                {{ $ap->segmentsNames() }}
                                                            @endif
                                                        </small>
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
                                                            <button
                                                                class="btn  {{ $ap->status->getButtonColor() }} dropdown-toggle"
                                                                type="button" id="dropdownMenuButton1"
                                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                                {{ $ap->status->getName() }}
                                                            </button>
                                                            <ul class="dropdown-menu"
                                                                aria-labelledby="dropdownMenuButton1">
                                                                @can('update', $ap)
                                                                    @include('appointmentuser::components.appointmentlist.operationbutton')
                                                                @endcan
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @elseif($eachTime['appointment_user_id'] == null)
                                            <tr style="background-color: #f7dcdc;">
                                                <td class="alert text-center bg-info ">
                                                    {{ $key + 1 }}</td>
                                                <td>
                                                    {{ substr($eachTime['from'], 0, -3) }} -
                                                    {{ substr($eachTime['until'], 0, -3) }}
                                                </td>
                                                <td colspan="4" class="text-center">
                                                    <div class="d-flex align-items-center">
                                                        @if ($edited['status'])
                                                            <button type="button" style="width: fit-content"
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
                                                                data-time-start="10:30" data-time-end="10:45"
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
                                        @endif
                                        @if ($loop->last)
                                            <tr>
                                                <td colspan="6">
                                                    <div class="alert alert-avatar alert-primary alert-dismissible">
                                                        حضور تا ساعت
                                                        <strong>{{ substr($eachTime['until'], 0, -6) }}</strong> در روز
                                                        <strong>{{ verta($fetchData['selectedDate'])->format('l') }}</strong>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6">
                                            <div class="alert alert-avatar alert-warning alert-dismissible">
                                                زمان حضور برای این تاریخ تعیین نشده است!!
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <livewire:appointmentuser::admin.add-appointment.modal.service-and-doctor-modal :appId="$fetchData['appId']" :appTime="$fetchData['time']"
        :serviceId="$fetchData['service']->id" :placeId="$fetchData['place']" :segmentId="$fetchData['segment']"
        :key="'change-doctor-service-'.$fetchData['appId'].'-'.$fetchData['service']->id.'-'.$fetchData['place']" />
    <livewire:appointmentuser::admin.add-appointment.modal.specific-day-appointment-registration-modal :appId="$fetchData['appId']"
        :appTime="$fetchData['time']" :serviceId="$fetchData['service']->id" :placeId="$fetchData['place']" :segmentId="$fetchData['segment']"
        :key="'register-appointment-'.$fetchData['appId'].'-'.$fetchData['service']->id.'-'.$fetchData['place']" />
    <div>
        @include('appointmentuser::components.appointmentlist.disapprovemodal')
    </div>
</div>
@push('style')
    {{-- <style>
        .jdp-container .iran-holiday {
            background-color: #e78e8e;
            color: white;
        }
    </style> --}}
@endpush
@push('scripts')
    <!-- SELECT2 JS -->
    <script src="{{ admin_asset('plugins/select2/select2.full.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/admin.sweetalert.js') }}"></script>
    <script src="{{ admin_asset('plugins/treeview/treeview.js') }}"></script>
    <script>
        $(document).ready(function() {
            var setAppModal = document.querySelector('#RegistrAnAppointment');
            var setAppModalInst = bootstrap.Modal.getOrCreateInstance(setAppModal);
            var myModalEl = document.querySelector('#changeDocmodal');
            var modal = bootstrap.Modal.getOrCreateInstance(myModalEl);

            function addJs() {
                const iranianHolidays = @json(holidays_array());
                jalaliDatepicker.startWatch({
                    zIndex: 99999,
                    dayRendering: function(dayOptions, input) {
                        const formatted =
                            `${dayOptions.year}/${String(dayOptions.month).padStart(2, '0')}/${String(dayOptions.day).padStart(2, '0')}`;
                        const isHoliday = iranianHolidays.includes(formatted);
                        return {
                            isHollyDay: isHoliday,
                        };
                    }
                });
                $(document).off('input.appointmentDay', '#currentDate').on('input.appointmentDay', '#currentDate', function() {
                    let selectedDate = $(this).val();
                    let seterValue = $(this).data('name');
                    @this.set(seterValue, selectedDate);
                });
            };
            addJs();
            Livewire.on('lunchRegisterModal', function() {
                setAppModalInst.show();
            });
            Livewire.on('lunchModal', function() {
                setTimeout(() => {
                    var myModal = new bootstrap.Modal(document.getElementById(
                        'resoanForDisapproveModal'), {
                        keyboard: false
                    });
                    myModal.show();
                }, 1000);
            });
            if ({{ $fetchData['showRegisterModal'] }}) {
                @this.dateHasBeenChange();
                setTimeout(() => {
                    setAppModalInst.show();
                }, 1000);
            };
            Livewire.on('closeModal', function() {
                modal.hide();
                setAppModalInst.hide();
            });
            Livewire.on('urlDateChange', function(newDate) {
                var currentUrl = window.location.href;
                var baseUrl = currentUrl.split('/').slice(0, -1).join('/');
                var newUrl = baseUrl + '/' + newDate.newDate;
                window.history.pushState({
                    path: newUrl
                }, '', newUrl);
            })
            const payment_link = @json($fetchData['secretary_send_payment_link']);
            if (payment_link) {
                $('body').on('change', '.payment_pending_input', function() {
                    if ($(this).val() == 'false') {
                        $('#sendSubmitPaymentStatus').removeClass('d-none');
                    } else {
                        $('#sendSubmitPaymentStatus').addClass('d-none');
                    }
                });
            }
        });
    </script>
@endpush
