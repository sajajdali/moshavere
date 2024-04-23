<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">لیست نوبت های ثبت شده</h1>
        </div>
        <a href="{{ route('admin.appointment.doctor.list') }}" class="btn btn-primary" aria-expanded="false"
            aria-controls="customDate">افزودن نوبت</a>
    </div>
    @include('admin::layouts.components.alert')
    @error('exelError')
        <div class="col-md-12 alert alert-danger fade show" role="alert">
            <i class="fa fa-remove me-2" aria-hidden="true"></i>
            {{ $message }}
        </div>
    @enderror
    <div class="row row-sm" wire:key='{{ \uniqid() }}'>
        <div class="col-lg-12">
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between border-bottom">
                    <h3 class="card-title">لیست نوبت های ثبت شده</h3>
                    <div class="card-options">
                        @can('[update,delete]', $this->handleSearch()->first())
                            <div class="btn-group me-2 d-none " id="exutebtn">
                                <button type="button" class="btn btn-success dropdown-toggle " data-bs-toggle="dropdown">
                                    عملیات گروهی <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu pe-4" role="menu">
                                    <li><a href="#" class="confirm_swal_alert w-100"
                                            data-description="از کنسل کردن نوبت های انتخابی مطمعن هستید؟"
                                            data-title="کنسل کردن" data-confirmbtn="بله کنسل شوند"
                                            data-action="GroupCancel">کنسل کردن</a>
                                    </li>
                                </ul>
                            </div>
                        @endcan
                        <button class="btn btn-primary" type="button" data-bs-toggle="collapse"
                            data-bs-target="#advanceSearch" aria-expanded="false" aria-controls="advanceSearch">
                            جست و جوی پیشرفته
                        </button>
                        @foreach ($search as $key => $value)
                            @if ($value !== null)
                                <button class="btn btn-secondary ms-2" wire:click="resetProperties" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#advanceSearch" aria-expanded="false"
                                    aria-controls="advanceSearch"
                                    wire:loading.class="bg-gray btn-loading disabled">نمایش
                                    همه
                                </button>
                            @break
                        @endif
                    @endforeach
                </div>

            </div>
            {{-- search cards --}}
            <div class="card-body">
                <div class="mb-5 collapse  @foreach ($search as $key => $value)
                            @if ($value !== null) show @break @endif @endforeach "
                    id="advanceSearch" wire:ignore.self>
                    <form class="form-horizontal example" autocomplete="off">
                        <div class="row mb-5">
                            <div class="col-12 col-md-3">
                                <h4 class="text-center text-primary text-start ms-1"><a data-bs-toggle="collapse"
                                        href="#userDataCollaps" role="button" aria-expanded="false"
                                        aria-controls="userDataCollaps" href="">
                                        <i class="fa fa-user" aria-hidden="true"></i>
                                        <span>مشخصات کاربر</span>
                                    </a></h4>
                            </div>
                            <div class=" col-12 col-md-9">
                                <hr class="my-4">
                            </div>
                            <div class="collapse  show row" id="userDataCollaps">
                                <div class="col-md-6 form-group">
                                    <label for="search-id" class=" form-label"><strong>ایدی</strong></label>
                                    <input class="form-control" id="search-id" wire:model="search.user_id"
                                        placeholder="ایدی کاربر مورد نظر" type="text">

                                </div>
                                <div class="col-md-6">
                                    <label for="search-Username" class="form-label"><strong>نام</strong></label>
                                    <input class="form-control" id="search-Username"
                                        wire:model="search.user_first_name" placeholder="نام کاربر مورد نظر"
                                        type="text">

                                </div>
                                <div class="col-md-6">
                                    <label for="search-UserLname" class="form-label"><strong>نام
                                            خانوادگی</strong></label>
                                    <input class="form-control" id="search-UserLname"
                                        wire:model="search.user_last_name" placeholder="نام خانوادگی کاربر مورد نظر"
                                        type="text">

                                </div>
                                <div class="col-md-6">
                                    <label for="search-UserMobile" class="form-label"><strong>شماره
                                            موبایل</strong></label>
                                    <input class="form-control" id="search-UserMobile"
                                        wire:model="search.user_mobile" placeholder="شماره تماس" type="text">

                                </div>
                            </div>
                        </div>
                        <div class="row my-5">
                            <div class="col-12 col-md-3">
                                <h4 class="text-center text-primary text-start ms-1"><a data-bs-toggle="collapse"
                                        href="#appointmentCollapsSearch" role="button" aria-expanded="false"
                                        aria-controls="appointmentCollapsSearch">
                                        <i class="fa fa-calendar-check-o" aria-hidden="true"></i>
                                        <span>فیلتر نوبت</span>
                                    </a></h4>
                            </div>
                            <div class="col-12 col-md-9">
                                <hr class="my-4">
                            </div>
                            <div class="collapse row
                            @if (isset($search['appointment_date']) ||
                                    isset($search['appointment_set_date']) ||
                                    isset($search['appointment_star_date']) ||
                                    isset($search['appointment_end_date'])) show @endif"
                                id="appointmentCollapsSearch" wire:ignore.self>
                                <div class="col-md-6">
                                    <label for="search-appointment_date" class="form-label"><strong>زمان
                                            نوبت</strong></label>
                                    <input class="form-control" id="search-appointment_date"
                                        wire:model="search.appointment_date"
                                        placeholder="زمانی که نوبت دریافت شده" type="text">

                                </div>
                                <div class="col-md-6">
                                    <label for="search-id-appointment_set_date" class="form-label"><strong>زمان
                                            ثبت
                                            نوبت</strong></label>
                                    <input class="form-control" id="search-appointment_set_date"
                                        wire:model="search.appointment_set_date"
                                        placeholder="زمانی که نوبت ثبت شده" type="text">

                                </div>
                                <div class="col-md-6">
                                    <label for="search-id-appointment_star_date" class="form-label"><strong>تاریخ
                                            شروع</strong></label>
                                    <input class="form-control" id="search-appointment_star_date"
                                        wire:model="search.appointment_star_date"
                                        placeholder="نوبت های از این تاریخ به بعد" type="text">

                                </div>
                                <div class="col-md-6">
                                    <label for="search-id-appointment_end_date" class="form-label"><strong>تاریخ
                                            پایان</strong></label>
                                    <input class="form-control" id="search-appointment_end_date"
                                        wire:model="search.appointment_end_date"
                                        placeholder="نوبت هایی ازین تاریخ به قبل" type="text">

                                </div>
                                <div class="col-md-6">
                                    <label for="search-appStatusId" class="form-label datePicker"><strong>وضعیت
                                            نوبت</strong></label>
                                    <select class="form-control" id="search-appStatusId"
                                        wire:model="search.AppointmentStatus" placeholder="نام ثبت نوبت"
                                        type="text">
                                        <option value="">انتخاب کنید...</option>
                                        @foreach (Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::cases() as $enumCase)
                                            <option value="{{ $enumCase }}">
                                                {{ $enumCase->getName() }}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>
                                <div class="col-md-6">
                                    <label for="search-docNumberId" class="form-label"><strong>شماره
                                            پرونده</strong></label>
                                    <input class="form-control" id="search-docNumberId"
                                        wire:model="search.docNumber" placeholder="ایدی رژیم مورد نظر"
                                        type="text">

                                </div>
                            </div>
                        </div>
                        <div class="row my-5">
                            <div class="col-12 col-md-4">
                                <h4 class="text-center text-primary text-start ms-1"> <a type="button"
                                        data-bs-toggle="collapse" data-bs-target="#settAppointmentCollaps"
                                        aria-expanded="false" aria-controls="settAppointmentCollaps">
                                        <i class="fa fa-user" aria-hidden="true"></i>
                                        <span> ثبت کننده نوبت</span>
                                    </a></h4>
                            </div>
                            <div class="col-12 col-md-8">
                                <hr class="my-4">
                            </div>
                            <div class="collapse row" id="settAppointmentCollaps">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label"><strong>ثبت کننده را انتخاب کنید</strong></label>
                                        <select wire:model='search.setterAppointment'
                                            class="form-control select2-show-search form-select"
                                            data-placeholder="انتخاب کنید..">
                                            <option label="انتخاب کنید.."></option>
                                            @foreach ($fetchData['appointmentSetter'] as $key => $user)
                                                <option value="{{ $user->id }}">{{ $user->fullName }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row my-5">
                            <div class="col-12 col-md-3">
                                <h4 class="text-center text-primary text-start ms-1"><a data-bs-toggle="collapse"
                                        href="#sectionCollaps" role="button" aria-expanded="false"
                                        aria-controls="sectionCollaps">
                                        <i class="fa fa-ambulance" aria-hidden="true"></i>
                                        <span>فیلتر بخش</span>
                                    </a></h4>
                            </div>
                            <div class="col-12 col-md-9">
                                <hr class="my-4">
                            </div>
                            <div class="collapse row" id="sectionCollaps">
                                <div class="row mb-4 ps-5">
                                    <select class="form-control" id="search-section-statusId"
                                        wire:model="search.section_status" placeholder="انتخاب کنید"
                                        type="text">
                                        <option value="">انتخاب کنید...</option>
                                        @foreach ($fetchData['Services'] as $key => $service)
                                            <option value="{{ $service->id }}">{{ $service->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row my-5">
                            <div class="col-12 col-md-3">
                                <h4 class="text-center text-primary text-start ms-1"><a data-bs-toggle="collapse"
                                        href="#doctorSectionFillter" role="button" aria-expanded="false"
                                        aria-controls="doctorSectionFillter">
                                        <i class="fa fa-user-md" aria-hidden="true"></i>
                                        <span>فیلتر پزشک</span>
                                    </a></h4>
                            </div>
                            <div class="col-12 col-md-9">
                                <hr class="my-4">
                            </div>
                            <div class="collapse row" id="doctorSectionFillter">
                                <div class="row mb-4 ps-5">
                                    <select class="form-control" id="search-name" wire:model="search.Doc_id"
                                        placeholder="انتخاب کنید" type="text">
                                        <option value="">انتخاب کنید...</option>
                                        @foreach ($fetchData['doctors'] as $doctor)
                                            <option value="{{ $doctor->id }}">{{ $doctor->fullName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-primary" type="button" wire:click="startSearch"
                            wire:loading.class="bg-gray btn-loading disabled">جست و
                            جو
                        </button>
                    </form>
                </div>
                <div class="table-responsive mb-3">
                    <table class="table text-nowrap text-md-nowrap table-bordered text-center"
                        wire:loading.class="op-0-3">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">انتخاب</th>
                                <th scope="col">نوع نوبت</th>
                                <th scope="col">ثبت شده توسط</th>
                                <th scope="col">نام کاربر</th>
                                <th scope="col">شماره موبایل</th>
                                <th scope="col">شماره پرونده</th>
                                <th scope="col">نام پزشک</th>
                                <th scope="col">بخش </th>
                                <th scope="col">ساعت نوبت</th>
                                <th scope="col">تاریخ نوبت</th>
                                <th scope="col">تاریخ ثبت نوبت</th>
                                @can('update', $this->handleSearch()->first())
                                    <th scope="col">عملیات</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @if ($this->handleSearch()->isNotEmpty())
                                @foreach ($this->handleSearch() as $key => $ap)
                                    <tr class="text-center {{ $ap->getColor() }}"
                                        wire:key='appoimt_{{ $ap->id }}'>
                                        <td>{{ $ap->id }}</td>
                                        <td class="p-4">
                                            <label class="mt-1" for="checkbox-{{ $ap->id }}">
                                                <input wire:model='form.checkbox.{{ $ap->id }}'
                                                    class="checkbox" id="checkbox-{{ $ap->id }}"
                                                    type="checkbox" value="">
                                            </label>
                                        </td>
                                        <td class="{{ $ap->type->getclass() }}">
                                            {!! $ap->kind->getIcon() !!}
                                            {!! $ap->getbage() !!}
                                        </td>
                                        <td>
                                            @if (isset($ap->details[Modules\AppointmentUser\app\Models\AppointmentUser::DETAIL_APPOINTMENT_VIA]))
                                                {{ Modules\User\Entities\User::find($ap->details[Modules\AppointmentUser\app\Models\AppointmentUser::DETAIL_APPOINTMENT_VIA])->full_name }}
                                            @else
                                                'بیمار'
                                            @endif
                                        </td>
                                        <td>{{ $ap->user->full_name }}</td>
                                        <td>{{ $ap->user->mobile }}</td>
                                        <td>{{ $ap->user->document_number ?? '---' }}</td>
                                        <td>{{ $ap->doctor->full_name }}</td>
                                        <td>{{ $ap->service?->title }}</td>
                                        <td>
                                            {{ verta($ap->start_time)->format('H:i') }}
                                            <strong>
                                                الی
                                            </strong>
                                            {{ verta($ap->end_time)->format('H:i') }}
                                        </td>
                                        <td>{{ verta($ap->date_visit)->format('Y/m/d') }}</td>
                                        <td>{{ verta($ap->created_at)->format('Y/m/d') }}</td>
                                        @can('update', $ap)
                                            <td>
                                                <div class="btn-group mt-2 mb-2">
                                                    <button type="button" class="btn {{$ap->status->getButtonColor()}} dropdown-toggle"
                                                        data-bs-toggle="dropdown">
                                                        {{$ap->status->getName()}}
                                                         <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu" role="menu">

                                                        @if ($ap->status == Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_PENDING)
                                                            <li>
                                                                <a wire:click='ApproveOnlineAppointment({{ $ap->id }})'
                                                                    href="#" data-label="ویرایش">
                                                                    <i class="fa fa-check text-success"
                                                                        aria-hidden="true"></i>
                                                                    تایید نوبت
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a wire:click='disApproveOnlineAppointment({{ $ap->id }})'
                                                                    href="#" data-label="ویرایش">
                                                                    <i class="fa fa-ban text-danger"
                                                                        aria-hidden="true"></i>
                                                                    عدم تایید نوبت
                                                                </a>
                                                            </li>
                                                        @elseif($ap->status == Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_MONITORING)
                                                            <li>
                                                                <a wire:click='ApprovemonitoringAppointment({{ $ap->id }})'
                                                                    href="#" data-label="ویرایش">
                                                                    <i class="fa fa-check text-success"
                                                                        aria-hidden="true"></i>
                                                                    تایید نوبت
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a wire:click='disApprovemonitoringAppointment({{ $ap->id }})'
                                                                    href="#" data-label="ویرایش">
                                                                    <i class="fa fa-ban text-danger"
                                                                        aria-hidden="true"></i>
                                                                    عدم تایید نوبت
                                                                </a>
                                                            </li>
                                                        @elseif($ap->status == Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_DISAPPROVED || $ap->status == Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_CANCEL)
                                                        @else
                                                            <li><a href="#" data-label="ویرایش">
                                                                    <i class="fa fa-pencil-square-o"
                                                                        aria-hidden="true"></i>
                                                                    ویرایش زمان نوبت
                                                                </a>
                                                            </li>
                                                            @if ($ap->type !== Modules\AppointmentUser\Enum\AppointmentUserTypeEnum::BETWEEN_PATIENTS)
                                                                <li><a data-description="میخواهید نوبت به بین مریض تبدیل شود؟"
                                                                        data-title="تغییر وضعیت "
                                                                        data-confirmbtn="بله تغییر کند"
                                                                        data-action="changeType"
                                                                        data-id="{{ $ap->id }}"
                                                                        class="confirm_swal_alert" data-label="نوبت"
                                                                        href="">
                                                                        <i class="fa fa-retweet"
                                                                            aria-hidden="true"></i>
                                                                        تبدیل
                                                                        به نوبت بین مریض</a>
                                                                </li>
                                                            @endif
                                                            <li>
                                                                <a class="confirm_swal_alert" data-label="نوبت"
                                                                    data-description="از کنسل کردن نوبت مطمعن هستید؟"
                                                                    data-title="کنسل کردن "
                                                                    data-confirmbtn="بله کنسل شود"
                                                                    data-action="cancelWithSms"
                                                                    data-id="{{ $ap->id }}"
                                                                    data-id="{{ $ap->id }}" href="">
                                                                    <i class="fa fa-envelope-o"
                                                                        aria-hidden="true"></i>
                                                                    کنسل
                                                                    کردن <small>(با ارسال پیامک)</small>
                                                                </a>
                                                            </li>
                                                            <li><a class="confirm_swal_alert" data-label="نوبت"
                                                                    data-description="از کنسل کردن نوبت مطمعن هستید؟"
                                                                    data-title="کنسل کردن "
                                                                    data-confirmbtn="بله کنسل شود"
                                                                    data-action="cancelWithOutSms
                                                                data-id="{{ $ap->id }}"
                                                                    href="">
                                                                    <i class="fa fa-times" aria-hidden="true"></i>
                                                                    کنسل
                                                                    کردن <small>(بدون ارسال پیامک)</small></a>
                                                            </li>
                                                        @endif
                                                        @can('delete', $ap)
                                                            <li><a class="confirm_swal_alert" data-label="نوبت"
                                                                    data-description="از کنسل و حذف کردن نوبت مطمعن هستید؟"
                                                                    data-title="کنسل و حذف  کردن "
                                                                    data-confirmbtn="بله کنسل و حذف شود"
                                                                    data-action="delete" data-id="{{ $ap->id }}"
                                                                    href="">
                                                                    <i class="fa fa-trash text-danger"
                                                                        aria-hidden="true"></i>
                                                                    کنسل و
                                                                    حذف نوبت
                                                                </a>
                                                            </li>
                                                        @endcan
                                                    </ul>
                                                </div>
                                            </td>
                                        @endcan
                                    </tr>
                                @endforeach
                            @else
                                <tr wire:key='no-appointments'>
                                    <td colspan="13">
                                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                                            <strong>توجه!</strong> نوبتی یافت نشد
                                            <a type="button" class="btn btn-info"
                                                href="{{ route('admin.appointment.doctor.list') }}">
                                                ثبت نوبت
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center">
                    {{ $this->handleSearch()->links() }}
                </div>
            </div>
        </div>
        <div class="text-end">
            <button wire:loading.class='btn-loading bg-gray' wire:target='ExportData' wire:click='ExportData'
                class="btn btn-info">دانلود خروجی اکسل</button>
        </div>
    </div>
    <div>
        @include('appointmentuser::components.appointmentlist.disapprovemodal')
    </div>
</div>
</div>
@push('scripts')
<script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script>
<script src="{{ admin_asset('plugins/sweet-alert/admin.sweetalert.js') }}"></script>
<script src="{{ admin_asset('plugins/select2/select2.full.min.js') }}"></script>
<script>
    $(document).ready(function() {
        function js() {
            $('.checkbox').change(function() {
                if ($('.checkbox:checked').length > 0) {
                    $('#exutebtn').removeClass('d-none');
                    $('#exutebtn').fadeIn();
                } else {
                    $('#exutebtn').fadeOut();
                    $('#exutebtn').addClass('d-none');
                }
            });
            $('.select2-show-search').select2();
            $('#search-appointment_date').persianDatepicker({
                initialValue: false,
                format: 'L',
                autoClose: true,
                onSelect: function(unix) {
                    @this.set('search.appointment_date', $('#search-appointment_date').val());
                }
            });
            $('#search-appointment_set_date').persianDatepicker({
                initialValue: false,
                format: 'L',
                autoClose: true,
                onSelect: function(unix) {
                    @this.set('search.appointment_set_date', $('#search-appointment_set_date')
                        .val());
                }
            });
            $('#search-appointment_end_date').persianDatepicker({
                initialValue: false,
                format: 'L',
                autoClose: true,
                onSelect: function(unix) {
                    @this.set('search.appointment_end_date', $('#search-appointment_end_date')
                        .val());
                }
            });
            $('#search-appointment_star_date').persianDatepicker({
                initialValue: false,
                format: 'L',
                autoClose: true,
                onSelect: function(unix) {
                    @this.set('search.appointment_star_date', $('#search-appointment_star_date')
                        .val());
                }
            });
        }
        js();
        Livewire.on('loadJs', function() {
            setTimeout(() => {
                js();
            }, 500);
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
    });
</script>
@endpush
