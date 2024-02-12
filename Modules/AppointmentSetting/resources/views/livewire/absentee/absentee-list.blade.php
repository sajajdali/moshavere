<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">لیست عدم حضور های ثبت شده</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <button class="btn btn-info">افزودن</button>
        </div>
    </div>
    @include('admin::layouts.components.alert')
    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between border-bottom">
                    <h3 class="card-title">لیست حضور های ثبت شده</h3>
                    <div class="card-options">
                        <button class="btn btn-primary" type="button" data-bs-toggle="collapse"
                            data-bs-target="#advanceSearch" aria-expanded="false" aria-controls="advanceSearch">
                            جست و جوی پیشرفته
                        </button>
                        @if (isset($search['id']) || isset($search['absenteeName']) || isset($search['status']))
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
                                        placeholder="ایدی رژیم مورد نظر" type="text">
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="search-name" class="col-md-2 form-label">نام پزشک</label>
                                <div class="col-md-10">
                                    <input class="form-control" id="search-name" wire:model="search.absenteeName"
                                        placeholder="نام رژیم" type="text">
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="search-name" class="col-md-2 form-label">نام بخش</label>
                                <div class="col-md-10">
                                    <input class="form-control" id="search-name" wire:model="search.absenteeName"
                                        placeholder="نام رژیم" type="text">
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
                                    <th scope="col">نام پزشک</th>
                                    <th scope="col">بخش ها</th>
                                    <th scope="col">تاریخ شروع</th>
                                    <th scope="col">تاریخ پایان</th>
                                    <th scope="col">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-center">
                                    <td>1</td>
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
    <script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/admin.sweetalert.js') }}"></script>

    <script>
        var myCollapsible = document.getElementById('advanceSearch')
        myCollapsible.addEventListener('show.bs.collapse', function() {
            @this.set('searchPanel', 'show');
        });
        myCollapsible.addEventListener('hide.bs.collapse', function() {
            @this.set('searchPanel', '');
        })
    </script>
@endpush
