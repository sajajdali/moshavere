<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">لیست نوبت های ثبت شده</h1>
        </div>
        <a href="{{ route('admin.appointment.add.sectionList') }}" class="btn btn-primary" aria-expanded="false"
            aria-controls="customDate">افزودن نوبت</a>
    </div>
    @include('admin::layouts.components.alert')

    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between border-bottom">
                    <h3 class="card-title">لیست نوبت های ثبت شده</h3>
                    <div class="card-options">
                        <button class="btn btn-primary" type="button" data-bs-toggle="collapse"
                            data-bs-target="#advanceSearch" aria-expanded="false" aria-controls="advanceSearch">
                            جست و جوی پیشرفته
                        </button>
                        @if (!empty($search))
                            <button class="btn btn-secondary ms-2" wire:click="resetProperties" type="button"
                                data-bs-toggle="collapse" data-bs-target="#advanceSearch" aria-expanded="false"
                                aria-controls="advanceSearch" wire:loading.class="bg-gray btn-loading disabled">نمایش
                                همه
                            </button>
                        @endif
                    </div>

                </div>
                {{-- search cards --}}
                <div class="card-body">
                    <div class="mb-5 collapse {{ $searchPanel }}" id="advanceSearch" wire:ignore>
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
                                <div class="collapse row" id="appointmentCollapsSearch">
                                    <div class="col-md-6">
                                        <label for="search-appointment_date" class="form-label"><strong>زمان
                                                نوبت</strong></label>
                                        <input class="form-control" id="search-appointment_date"
                                            wire:model="search.appointment_date" wire:ignore
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
                                        <label for="search-paymentTypeId" class="form-label"><strong>نوع
                                                پرداخت</strong></label>
                                        <input class="form-control" id="search-paymentTypeId"
                                            wire:model="search.paymentType" placeholder="نوع پرداخت انجام شده"
                                            type="text">

                                    </div>
                                    <div class="col-md-6">
                                        <label for="search-docNumberId" class="form-label"><strong>شماره
                                                پرونده</strong></label>
                                        <input class="form-control" id="search-docNumberId"
                                            wire:model="search.docNumber" placeholder="ایدی رژیم مورد نظر"
                                            type="text">

                                    </div>
                                    <div class="col-12">
                                        <label for="search-appStatusId" class="form-label datePicker">وضعیت
                                            نوبت</label>
                                        <select class="form-control" id="search-appStatusId"
                                            wire:model="search.AppointmentStatus" placeholder="نام ثبت نوبت"
                                            type="text">
                                            <option value="">ثبت شده</option>
                                            <option value="">در انتظار تایید</option>
                                            <option value="">کنسل شده</option>
                                            <option value="">حضور</option>
                                            <option value="">عدم حضور</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row my-5">
                                <div class="col-12 col-md-4">
                                    <h4 class="text-center text-primary text-start ms-1"> <a type="button"
                                            data-bs-toggle="collapse" data-bs-target="#settAppointmentCollaps"
                                            aria-expanded="false" aria-controls="settAppointmentCollaps">
                                            <i class="fa fa-user" aria-hidden="true"></i>
                                            <span>مشخصات ثبت کننده نوبت</span>
                                        </a></h4>
                                </div>
                                <div class="col-12 col-md-8">
                                    <hr class="my-4">
                                </div>
                                <div class="collapse row" id="settAppointmentCollaps">
                                    <div class="col-md-6">
                                        <label for="search-SetAppId-id"
                                            class="form-label"><strong>ایدی</strong></label>
                                        <input class="form-control" id="search-SetAppId-id"
                                            wire:model="search.SetApp-id" placeholder="ایدی رژیم مورد نظر"
                                            type="text">

                                    </div>
                                    <div class="col-md-6">
                                        <label for="search-SetApp-nameId"
                                            class="form-label"><strong>نام</strong></label>
                                        <input class="form-control" id="search-SetApp-nameId"
                                            wire:model="search.SetApp-Firstname" placeholder="ایدی رژیم مورد نظر"
                                            type="text">

                                    </div>
                                    <div class="col-md-6">
                                        <label for="search-LastNameId" class="form-label"><strong>نام
                                                خانوادگی</strong></label>
                                        <input class="form-control" id="search-SetApp-LastNameId"
                                            wire:model="search.SetApp-LastName" placeholder="ایدی رژیم مورد نظر"
                                            type="text">

                                    </div>
                                    <div class="col-md-6">
                                        <label for="search-SetApp-mobileId" class="form-label"><strong>شماره
                                                همراه</strong></label>
                                        <input class="form-control" id="search-SetApp-mobileId"
                                            wire:model="search.SetApp-mobile" placeholder="ایدی رژیم مورد نظر"
                                            type="text">

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
                                            wire:model="search.section-status" placeholder="انتخاب کنید"
                                            type="text">
                                            <option value="">ویزیت</option>
                                            <option value="">جراحی</option>
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
                                        <select class="form-control" id="search-name"
                                            wire:model="search.Doc-first_name" placeholder="انتخاب کنید"
                                            type="text">
                                            <option value="">ممد</option>
                                            <option value="">اصغرر</option>
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
                                    <th scope="col">ثبت با</th>
                                    <th scope="col">ثبت شده توسط</th>
                                    <th scope="col">نام کاربر</th>
                                    <th scope="col">شماره موبایل</th>
                                    <th scope="col">شماره پرونده</th>
                                    <th scope="col">نام پزشک</th>
                                    <th scope="col">بخش </th>
                                    <th scope="col">تاریخ نوبت</th>
                                    <th scope="col">تاریخ ثبت</th>
                                    <th scope="col">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-center table-success">
                                    <td class="bg-">1</td>
                                    <td class="p-4">
                                        <label class="mt-1" for="checkbox-1">
                                            <input class="" id="checkbox-1" type="checkbox" value=""
                                                checked="">
                                        </label>
                                    </td>
                                    <td>
                                        <i class="fa fa-laptop fa-2x" aria-hidden="true"></i>
                                    </td>
                                    <td>ادمسین</td>
                                    <td>اصغر</td>
                                    <td>093760208222</td>
                                    <td>22</td>
                                    <td>ممد</td>
                                    <td>ویزیت</td>
                                    <td>1402/01/25</td>
                                    <td>1402/01/28</td>
                                    <td>
                                        <div class="btn-group mt-2 mb-2">
                                            <button type="button" class="btn btn-primary dropdown-toggle"
                                                data-bs-toggle="dropdown">
                                                عملیات <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="#" data-label="ویرایش">ویرایش</a>
                                                </li>
                                                <li><a class="delete_confirm_alert" href="#"
                                                        data-label="ویرایش">حذف</a>
                                                </li>
                                            </ul>
                                        </div>

                                    </td>
                                </tr>
                                <tr class="text-center table-danger">
                                    <td>2</td>
                                    <td class="p-4">
                                        <label class="mt-1" for="checkbox-1">
                                            <input class="" id="checkbox-1" type="checkbox" value=""
                                                checked="">
                                        </label>
                                    </td>
                                    <td>
                                        <i class="fa fa-phone fa-2x" aria-hidden="true"></i>
                                    </td>
                                    <td>ادمسین</td>
                                    <td>اصغر</td>
                                    <td>093760208222</td>
                                    <td>22</td>
                                    <td>ممد</td>
                                    <td>ویزیت</td>
                                    <td>1402/01/25</td>
                                    <td>1402/01/28</td>
                                    <td>
                                        <div class="btn-group mt-2 mb-2">
                                            <button type="button" class="btn btn-primary dropdown-toggle"
                                                data-bs-toggle="dropdown">
                                                عملیات <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="#" data-label="ویرایش">ویرایش</a>
                                                </li>
                                                <li><a class="delete_confirm_alert" href="#"
                                                        data-label="ویرایش">حذف</a>
                                                </li>
                                            </ul>
                                        </div>

                                    </td>
                                </tr>
                                <tr class="text-center table-warning">
                                    <td>3</td>
                                    <td class="p-4">
                                        <label class="mt-1" for="checkbox-1">
                                            <input class="" id="checkbox-1" type="checkbox" value=""
                                                checked="">
                                        </label>
                                    </td>
                                    <td>
                                        <i class="fa fa-phone fa-2x" aria-hidden="true"></i>
                                    </td>
                                    <td>ادمسین</td>
                                    <td>اصغر</td>
                                    <td>093760208222</td>
                                    <td>22</td>
                                    <td>ممد</td>
                                    <td>ویزیت</td>
                                    <td>1402/01/25</td>
                                    <td>1402/01/28</td>
                                    <td>
                                        <div class="btn-group mt-2 mb-2">
                                            <button type="button" class="btn btn-primary dropdown-toggle"
                                                data-bs-toggle="dropdown">
                                                عملیات <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="#" data-label="ویرایش">ویرایش</a>
                                                </li>
                                                <li><a class="delete_confirm_alert" href="#"
                                                        data-label="ویرایش">حذف</a>
                                                </li>
                                            </ul>
                                        </div>

                                    </td>
                                </tr>
                                <tr class="text-center table-info">
                                    <td>3</td>
                                    <td class="p-4">
                                        <label class="mt-1" for="checkbox-1">
                                            <input class="" id="checkbox-1" type="checkbox" value=""
                                                checked="">
                                        </label>
                                    </td>
                                    <td>
                                        <i class="fa fa-phone fa-2x" aria-hidden="true"></i>
                                    </td>
                                    <td>ادمسین</td>
                                    <td>اصغر</td>
                                    <td>093760208222</td>
                                    <td>22</td>
                                    <td>ممد</td>
                                    <td>ویزیت</td>
                                    <td>1402/01/25</td>
                                    <td>1402/01/28</td>
                                    <td>
                                        <div class="btn-group mt-2 mb-2">
                                            <button type="button" class="btn btn-primary dropdown-toggle"
                                                data-bs-toggle="dropdown">
                                                عملیات <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="#" data-label="ویرایش">ویرایش</a>
                                                </li>
                                                <li><a class="delete_confirm_alert" href="#"
                                                        data-label="ویرایش">حذف</a>
                                                </li>
                                            </ul>
                                        </div>

                                    </td>
                                </tr>
                                <tr class="text-center table-secondary">
                                    <td>3</td>
                                    <td class="p-4">
                                        <label class="mt-1" for="checkbox-1">
                                            <input class="" id="checkbox-1" type="checkbox" value=""
                                                checked="">
                                        </label>
                                    </td>
                                    <td>
                                        <i class="fa fa-phone fa-2x" aria-hidden="true"></i>
                                    </td>
                                    <td>ادمسین</td>
                                    <td>اصغر</td>
                                    <td>093760208222</td>
                                    <td>22</td>
                                    <td>ممد</td>
                                    <td>ویزیت</td>
                                    <td>1402/01/25</td>
                                    <td>1402/01/28</td>
                                    <td>
                                        <div class="btn-group mt-2 mb-2">
                                            <button type="button" class="btn btn-primary dropdown-toggle"
                                                data-bs-toggle="dropdown">
                                                عملیات <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="#" data-label="ویرایش">ویرایش</a>
                                                </li>
                                                <li><a class="delete_confirm_alert" href="#"
                                                        data-label="ویرایش">حذف</a>
                                                </li>
                                            </ul>
                                        </div>

                                    </td>
                                </tr>
                                <tr class="text-center table-light">
                                    <td>3</td>
                                    <td class="p-4">
                                        <label class="mt-1" for="checkbox-1">
                                            <input class="" id="checkbox-1" type="checkbox" value=""
                                                checked="">
                                        </label>
                                    </td>
                                    <td>
                                        <i class="fa fa-phone fa-2x" aria-hidden="true"></i>
                                    </td>
                                    <td>ادمسین</td>
                                    <td>اصغر</td>
                                    <td>093760208222</td>
                                    <td>22</td>
                                    <td>ممد</td>
                                    <td>ویزیت</td>
                                    <td>1402/01/25</td>
                                    <td>1402/01/28</td>
                                    <td>
                                        <div class="btn-group mt-2 mb-2">
                                            <button type="button" class="btn btn-primary dropdown-toggle"
                                                data-bs-toggle="dropdown">
                                                عملیات <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="#" data-label="ویرایش">ویرایش</a>
                                                </li>
                                                <li><a class="delete_confirm_alert" href="#"
                                                        data-label="ویرایش">حذف</a>
                                                </li>
                                            </ul>
                                        </div>

                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="100%" class="text-center">
                                        <div class="alert alert-info">
                                            هیچ موردی یافت نشد
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    {{-- <div>
                        {{ $absentees->links() }}
                    </div> --}}
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
    <script>
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
                @this.set('search.appointment_set_date', $('#search-appointment_set_date').val());
            }
        });
    </script>
@endpush
