<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">لیست نوبت های ثبت شده</h1>
        </div>
        @can('appointment_user.addApp')
            <a href="{{ route('admin.appointment_user.addApp') }}" class=" mt-3 mt-md-0 btn btn-primary" aria-expanded="false"
                aria-controls="customDate">افزودن نوبت</a>
        @endcan
    </div>
    @include('admin::layouts.components.alert')
    @isset($msg)
        <div class="col-md-12 alert alert-success fade show" role="alert">
            <i class="fa fa-check-circle-o me-2" aria-hidden="true"></i>
            {{ $msg }}
        </div>
    @endisset
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
                                    wire:loading.class="bg-gray btn-loading disabled">نمایش همه نوبت ها
                                </button>
                            @break

                        @endif
                    @endforeach
                </div>

            </div>
            {{-- search cards --}}
            <div class="card-body">
                <div class="mb-5 collapse
                @if ($showcollaps) @foreach ($search as $key => $value)
                    @if ($key == 'kind')
                        @continue @endif
                        @if ($value !== null) show @break @endif
                    @endforeach "
                    @endif
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
                                <div class="col-md-12">
                                    <label for="search-UserMobile" class="form-label"><strong>کد
                                            ملی</strong></label>
                                    <input class="form-control" id="search-UserMobile"
                                        wire:model="search.national_code" placeholder="کد ملی کاربر"
                                        type="text">

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
                                    isset($search['kind']) ||
                                    isset($search['appointment_id']) ||
                                    isset($search['appointment_star_date']) ||
                                    isset($search['appointment_operatorId']) ||
                                    isset($search['appointment_end_date'])) ) show @endif"
                                id="appointmentCollapsSearch" wire:ignore.self>
                                <div class="col-md-6">
                                    <label for="search-appointment_id" class="form-label"><strong>ایدی</strong></label>
                                    <input class="form-control" id="search-appointment_id"
                                        wire:model="search.appointment_id"
                                        placeholder="آیدی نوبت" type="text">

                                </div>
                                <div class="col-md-6">
                                    <label for="search-kind" class="form-label datePicker"><strong>نوع
                                            نوبت</strong></label>
                                    <select class="form-control" id="search-kind" wire:model="search.kind"
                                        type="text">
                                        <option value="">انتخاب کنید...</option>
                                        @foreach (Modules\AppointmentUser\Enum\AppointmentUserKindEnum::cases() as $kindCase)
                                            <option value="{{ $kindCase }}">
                                                {{ $kindCase->getName() }}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>
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
                                        wire:model="search.docNumber" placeholder="شماره پرونده کاربر"
                                        type="text">

                                </div>
                                @if (!empty(\Modules\User\Entities\User::operators()))
                                    <div class="col-md-6">
                                        <label for="search-operator" class="form-label datePicker"><strong>اپراتور
                                                نوبت</strong></label>
                                        <select class="form-control" id="search-operator"
                                            wire:model="search.appointment_operatorId"
                                            placeholder="وضعیت اپراتور نوبت" type="text">
                                            <option value="">انتخاب کنید...</option>
                                            <option value="0">بدون اپراتور</option>

                                            @foreach (\Modules\User\Entities\User::operators() as $operators)
                                                <option value="{{ $operators->id }}">
                                                    {{ $operators->fullName }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
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
                            <div class="collapse row @if(isset($search['setterAppointment'])) show @endif" id="settAppointmentCollaps">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label"><strong>ثبت کننده را انتخاب کنید</strong></label>
                                        <select wire:model='search.setterAppointment'
                                            class="form-control select2-show-search form-select"
                                            data-id="setterAppointment"
                                            data-placeholder="انتخاب کنید..">
                                            <option label="انتخاب کنید.."></option>
                                            @if (isset($fetchData['appointmentSetter']))
                                                @foreach ($fetchData['appointmentSetter'] as $key => $role)
                                                    <option value="{{ $role->id }}">{{ $role->name }}
                                                    </option>
                                                @endforeach
                                            @endif
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
                            <div class="collapse row @if(isset($search['Doc_id'])) show @endif" id="doctorSectionFillter">
                                <div class="row mb-4 ps-5">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-label"><strong>پزشک</strong></label>
                                            <select wire:model='search.Doc_id' wire:igonre.self
                                                class="form-control select2-show-search form-select"
                                                data-id="Doc_id" data-placeholder="انتخاب کنید..">
                                                <option label="انتخاب کنید.."></option>
                                                @if (isset($fetchData['doctors']))
                                                    @foreach ($fetchData['doctors'] as $doctor)
                                                        <option value="{{ $doctor->id }}">
                                                            {{ $doctor->fullName }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
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
                                <th scope="col">نام کاربر</th>
                                <th scope="col">شماره موبایل</th>
                                <th scope="col">نام پزشک</th>
                                <th scope="col">ساعت نوبت</th>
                                <th scope="col">تاریخ نوبت</th>
                                <th scope="col">عملیات</th>
                                <th scope="col">ثبت شده توسط</th>
                                <th scope="col">بخش </th>
                                <th scope="col">تاریخ ثبت نوبت</th>
                                <th scope="col">کد ملی</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($this->handleSearch()->isNotEmpty())
                                @foreach ($this->handleSearch() as $key => $ap)
                                    <tr class="text-center {{ $ap->getColor() }}"
                                        wire:key='appoimt_{{ $ap->id }}'>
                                        <td>{{ $ap->id }}</td>
                                        <td class="p-4 ">
                                            <div class="d-flex flex-column">
                                                <label class="mt-1" for="checkbox-{{ $ap->id }}">
                                                    <input wire:model='form.checkbox.{{ $ap->id }}'
                                                        class="checkbox" id="checkbox-{{ $ap->id }}"
                                                        type="checkbox" value="">
                                                </label>
                                                @can('appointment_user.feedBack')
                                                    @if ($ap->feedbacks->isNotEmpty())
                                                        <a wire:click='lunchFeedBackModal({{ $ap->id }})'
                                                            href="#"><small class="badge bg-primary ">
                                                                نظر سنجی
                                                            </small></a>
                                                    @endif
                                                @endcan
                                            </div>
                                        </td>
                                        <td class="{{ $ap->type->getclass() }} d-flex flex-column">
                                            {!! $ap->kind->getIcon() !!}
                                            <a
                                                @if ($ap->kind == \Modules\AppointmentUser\Enum\AppointmentUserKindEnum::ONLINE && $ap->online->isNotEmpty()) href="{{ route('admin.appointment_user.message.detail', ['onlineAppId' => $ap->online->first()?->id]) }}" @else href="" @endif>
                                                <span
                                                    class="badge badge-sm {{ $ap->kind->getbadgeColor() }} rounded-pill">
                                                    {{ $ap->kind->getName() }}
                                                    @if ($ap->kind == \Modules\AppointmentUser\Enum\AppointmentUserKindEnum::ONLINE)
                                                        {{ $ap->online->first()?->messages?->first()?->unReadedMessageCount() ?? 0 }}
                                                    @endif
                                                </span>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span>
                                                    {{ $ap->user?->full_name ?? 'کاربر حذف شده' }}
                                                </span>
                                                @if (setting(\Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_USER_PERESENT_STATUS_REGISTRATION))
                                                    {!! $ap->attendedStatus() !!}
                                                @endif
                                            </div>
                                        </td>
                                        <td
                                            @if ($ap->isAppForothers()) class="text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="نوبت برای شخص دیگری دریافت شده است و شماره شخص وارد نشده است!" @endif>
                                            {{ $ap->user?->mobile ?? $ap->checkForRegisterForOthers() }}
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span>{{ $ap->doctor?->full_name ?? 'پزشک حذف شده' }}</span>
                                                @if (
                                                    $ap->setting?->detail[\Modules\AppointmentSetting\app\Models\AppointmentSetting::OPERATORS][
                                                        \Modules\AppointmentSetting\app\Models\AppointmentSetting::STATUS
                                                    ]
                                                )
                                                    <span
                                                        class="badge badge-sm bg-info">{{ $ap->operator?->full_name ?? 'بدون اپراتور' }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if ($ap->kind == \Modules\AppointmentUser\Enum\AppointmentUserKindEnum::IN_PERSION)
                                                {{ verta($ap->start_time)->format('H:i') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ verta($ap->date_visit)->format('Y/m/d') }}</td>

                                        <td>
                                            @canany(['update', 'delete'], $ap)
                                                <div class="btn-group mt-2 mb-2">
                                                    <button type="button"
                                                        class="btn {{ $ap->status->getButtonColor() }} dropdown-toggle"
                                                        data-bs-toggle="dropdown">
                                                        {{ $ap->status->getName() }}
                                                        <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu" role="menu">
                                                        @include('appointmentuser::components.appointmentlist.operationbutton')
                                                    </ul>
                                                </div>
                                            @else
                                                <div class="btn-group mt-2 mb-2">
                                                    <button type="button" class="btn btn-default dropdown-toggle"
                                                        data-bs-toggle="dropdown">
                                                        عملیات <span class="caret"></span>
                                                    </button>
                                                </div>
                                            @endcan
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                @if ($ap->agent)
                                                    <span> {{ $ap->agent->fullName }}</span>
                                                @else
                                                    <span>خود کاربر</span>
                                                @endif
                                                @if ($ap->kind == \Modules\AppointmentUser\Enum\AppointmentUserKindEnum::ONLINE && $ap->hasAgent())
                                                    <small class="badge bg-light rounded-pill">
                                                        <span> {{ $ap->confirm_or_reject_by() }}</span>
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $ap->service?->title ?? 'سرویس حذف شده ' }}</td>
                                        <td>
                                            <div class="d-flex flex-column align-item-center">
                                                <span>
                                                    {{ verta($ap->created_at)->format('Y/m/d') }}
                                                </span>
                                                <span>
                                                    {{ verta($ap->created_at)->format('H:i') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>{{ $ap->user?->national_code ?? '---' }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr wire:key='no-appointments'>
                                    <td colspan="13">
                                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                                            <strong>توجه!</strong> نوبتی یافت نشد
                                            <a type="button" class="btn btn-info"
                                                href="{{ route('admin.appointment_user.addApp') }}">
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
        @include('appointmentuser::components.appointmentlist.feedbackmodal')
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
            $('body').on('change', '.select2-show-search', function() {
                var modelName = $(this).data('id');
                console.log('search.' + modelName);

                @this.set('search.' + modelName, $(this).val());
            });
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
        Livewire.on('lunchFeedBackModal', function() {
            setTimeout(() => {
                var feedBackModal = new bootstrap.Modal(document.getElementById(
                    'feedBackModal'), {
                    keyboard: false
                });
                feedBackModal.show();
            }, 1000);
        });
        Livewire.on('exelError', function() {
            setTimeout(() => {
                swal("توجه!",
                    "تعداد داده ها زیاد است! لطفا با استفاده از جست و جو تعداد داده ها را محدود کنید",
                    "warning");
                $('html, body').animate({
                    scrollTop: 0
                }, '50');
            }, 1000);
        });
    });
</script>
@endpush
