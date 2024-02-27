<div>
    <div>
        <div class="page-header">
            <div>
                <h1 class="page-title">لیست مطب ها</h1>
            </div>
            <div class="ms-auto pageheader-btn">
                <a href="{{ route('admin.service.create') }}" class="btn btn-info">افزودن بخش جدید</a>
            </div>
        </div>
        @include('admin::layouts.components.alert')
        <div class="row row-sm">
            <div class="col-lg-12">
                <div class="card custom-card">
                    <div class="card-header d-flex justify-content-between border-bottom">
                        <h3 class="card-title">مدیریت مطب ها</h3>
                        <div class="card-options">
                            <button class="btn btn-primary" type="button" data-bs-toggle="collapse"
                                data-bs-target="#advanceSearch" aria-expanded="false" aria-controls="advanceSearch">
                                جست و جوی پیشرفته
                            </button>
                            @if (! empty($search))
                                <button class="btn btn-secondary ms-2" type="button" wire:click="resetProperties"
                                    wire:loading.class="bg-gray btn-loading disabled">نمایش همه
                                </button>
                            @endif
                        </div>

                    </div>
                    <div class="card-body">
                        <div class="mb-5 collapse {{ $searchPanel }}" id="advanceSearch" wire:ignore>
                            <form class="form-horizontal example" autocomplete="off">
                                <div class="row mb-4">
                                    <label for="search-id" class="col-md-2 form-label">ایدی</label>
                                    <div class="col-md-10">
                                        <input class="form-control" id="search-id" wire:model="search.id"
                                            placeholder="ایدی مطب مورد نظر" type="text">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <label for="search-name" class="col-md-2 form-label">نام مطب</label>
                                    <div class="col-md-10">
                                        <input class="form-control" id="search-name" wire:model="search.specialityName"
                                            placeholder="نام مطب" type="text">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <label class="form-label col-md-2" for="default-dropdown">وضعیت</label>
                                    <div class="col-md-10">
                                        <select wire:model='search.status' name="country"
                                            class="form-control form-select" id="default-dropdown"
                                            data-bs-placeholder="انتخاب کنید..">
                                            <option label="انتخاب کنید"></option>
                                            <option>
                                                فعال</option>
                                            <option>
                                                غیرفعال</option>
                                        </select>
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
                                        <th scope="col">نام مطب</th>
                                        <th scope="col">وضعیت</th>
                                        <th scope="col">تعداد پزشکان</th>
                                        <th scope="col">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="text-center">
                                        <td>1</td>
                                        <td>
                                            سعادت آباد
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill bg-success p-3">فعال</span>
                                        </td>
                                        <td>3</td>
                                        <td>
                                            <div class="btn-group mt-2 mb-2">
                                                <button type="button" class="btn btn-primary dropdown-toggle"
                                                    data-bs-toggle="dropdown">
                                                    عملیات <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu" role="menu">
                                                    <li><a class="delete_confirm_alert" data-label="حذف " data-id="3"
                                                            href="#">حذف</a>
                                                    </li>
                                                    <li><a href="#" data-label="ویرایش">ویرایش</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="text-center">
                                        <td>2</td>
                                        <td>نیاوران</td>
                                        <td>
                                            <span class="badge rounded-pill bg-danger p-3">غیر فعال</span>
                                        </td>
                                        <td>1</td>
                                        <td>
                                            <div class="btn-group mt-2 mb-2">
                                                <button type="button" class="btn btn-primary dropdown-toggle"
                                                    data-bs-toggle="dropdown">
                                                    عملیات <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu" role="menu">
                                                    <li><a class="delete_confirm_alert" data-label="حذف "
                                                            data-id="3" href="#">حذف</a>
                                                    </li>
                                                    <li><a href="#" data-label="ویرایش">ویرایش</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
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
                        <div>
                            {{-- {{ $specialities->links() }} --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/admin.sweetalert.js') }}"></script>
@endpush
