<div>
    <div>
        <div class="page-header">
            <div>
                <h1 class="page-title">لیست کد های تخفیف</h1>
            </div>
            <div class="ms-auto pageheader-btn">
                <a href="{{ route('admin.discount.create') }}" class="btn btn-success">افزودن کد تخفیف جدید</a>
            </div>
        </div>
        @include('admin::layouts.components.alert')
        <div class="row row-sm">
            <div class="col-lg-12">
                <div class="card custom-card">
                    <div class="card-header d-flex justify-content-between border-bottom">
                        <h3 class="card-title">مدیریت کد های تخفیف </h3>
                        <div class="card-options">
                            <button class="btn btn-primary" type="button" data-bs-toggle="collapse"
                                data-bs-target="#advanceSearch" aria-expanded="false" aria-controls="advanceSearch">
                                جست و جوی پیشرفته
                            </button>
                            @if (isset($search['id']) || isset($search[ 'code']) || isset($search['active']))
                                <button class="btn btn-secondary ms-2" type="button" wire:click="resetProperties"
                                    data-bs-toggle="collapse" data-bs-target="#advanceSearch" aria-expanded="false"
                                    aria-controls="advanceSearch"
                                    wire:loading.class="bg-gray btn-loading disabled">نمایش همه
                                </button>
                            @endif
                        </div>

                    </div>
                    <div class="card-body">
                        <div class="mb-5 collapse
                        @if (!empty($search['id']) ||
                         !empty($search['code']) ||
                         !empty($search['active'])) show @endif" id="advanceSearch"
                            wire:ignore>
                            <form class="form-horizontal example" autocomplete="off">
                                <div class="row mb-4">
                                    <label for="search-id" class="col-md-2 form-label">ایدی</label>
                                    <div class="col-md-10">
                                        <input class="form-control" id="search-id" wire:model="search.id"
                                            discountholder="ایدی مطب مورد نظر" type="text">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <label for="search-name" class="col-md-2 form-label">کد تخفیف</label>
                                    <div class="col-md-10">
                                        <input class="form-control" id="search-name" wire:model="search.code"
                                            discountholder="کد" type="text">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <label class="form-label col-md-2" for="default-dropdown">وضعیت</label>
                                    <div class="col-md-10">
                                        <select wire:model='search.active' name="country"
                                            class="form-control form-select" id="default-dropdown"
                                            data-bs-discountholder="انتخاب کنید..">
                                            <option label="انتخاب کنید"></option>
                                            <option value="{{ App\Enum\ActiveEnum::ACTIVE }}">
                                                {{ App\Enum\ActiveEnum::ACTIVE->getName() }}</option>
                                            <option value="{{ App\Enum\ActiveEnum::DEACTIVE }}">
                                                {{ App\Enum\ActiveEnum::DEACTIVE->getName() }}</option>

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
                                        <th scope="col">بخش های قابل استفاده</th>
                                        <th scope="col">پزشکان قابل استفاده</th>
                                        <th scope="col">کد تخفیف</th>
                                        <th scope="col">تعداد استفاده شده</th>
                                        <th scope="col">تعداد قابل استفاده</th>
                                        <th scope="col">تاریخ شروع</th>
                                        <th scope="col">تاریخ پایان</th>
                                        <th scope="col">وضعیت</th>
                                        <th scope="col">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($discounts->isNotEmpty())
                                        @foreach ($discounts as $discount)
                                            <tr class="text-center">
                                                <td>{{ $discount->id }}</td>
                                                <td>
                                                    {{ $discount->services() }}
                                                </td>
                                                <td>
                                                    {{ $discount->doctors() }}
                                                </td>
                                                <td>
                                                    {{ $discount->code }}
                                                </td>
                                                <td>
                                                    {{ $discount->usage_counter }}
                                                </td>
                                                <td>
                                                    {{ $discount->detail[Modules\Discount\app\Models\Discount::DETAIL_TOTAL_USAGE] ?? 'تعریف نشده' }}
                                                </td>
                                                <td>
                                                    {{ verta($discount->start_at)->format('Y/m/d') }}
                                                </td>
                                                <td>
                                                    {{ verta($discount->end_at)->format('Y/m/d') }}
                                                </td>
                                                <td>
                                                    {!! $discount->active->getBadge() !!}
                                                </td>
                                                <td>

                                                    <div class="btn-group mt-2 mb-2">
                                                        <button type="button" class="btn btn-primary dropdown-toggle"
                                                            data-bs-toggle="dropdown">
                                                            عملیات <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu" role="menu">
                                                            @can('delete', $discount)
                                                                <li><a class="delete_confirm_alert" data-label="حذف "
                                                                        data-id="{{ $discount->id }}" href="#">حذف</a>
                                                                </li>
                                                            @endcan
                                                            @can('update', $discount)
                                                                <li><a href="{{ route('admin.discount.edit', ['discount' => $discount->id]) }}"
                                                                        data-label="ویرایش">ویرایش</a>
                                                                </li>
                                                            @endcan
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="100%" class="text-center">
                                                <div class="alert alert-info">
                                                    هیچ موردی یافت نشد
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div>
                            {{ $discounts->links() }}
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
