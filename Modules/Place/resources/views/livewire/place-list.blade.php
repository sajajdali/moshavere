<div>
    <div>
        <div class="page-header">
            <div>
                <h1 class="page-title">لیست مطب ها</h1>
            </div>
            <div class="ms-auto pageheader-btn">
                @can('create', Modules\Place\app\Models\Place::class)
                    <a href="{{ route('admin.place.create') }}" class="btn btn-success">افزودن مطب جدید</a>
                @endcan
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
                            @if (!empty($search['id']) || !empty($search['placeName']) || !empty($search['active']))
                                <button class="btn btn-secondary ms-2" type="button" wire:click="resetProperties"
                                    data-bs-toggle="collapse" data-bs-target="#advanceSearch" aria-expanded="false"
                                    aria-controls="advanceSearch"
                                    wire:loading.class="bg-gray btn-loading disabled">نمایش همه
                                </button>
                            @endif
                        </div>

                    </div>
                    <div class="card-body">
                        <div class="mb-5 collapse @if (!empty($search['id']) || !empty($search['placeName']) || !empty($search['active'])) show @endif" id="advanceSearch"
                            wire:ignore>
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
                                        <input class="form-control" id="search-name" wire:model="search.placeName"
                                            placeholder="نام مطب" type="text">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <label class="form-label col-md-2" for="default-dropdown">وضعیت</label>
                                    <div class="col-md-10">
                                        <select wire:model='search.active' name="country"
                                            class="form-control form-select" id="default-dropdown"
                                            data-bs-placeholder="انتخاب کنید..">
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
                                        <th scope="col">نام مطب</th>
                                        <th scope="col">وضعیت</th>
                                        <th scope="col">تعداد پزشکان</th>
                                        <th scope="col">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($places->isNotEmpty())
                                        @foreach ($places as $place)
                                            <tr class="text-center">
                                                <td>{{ $place->id }}</td>
                                                <td>
                                                    {{ $place->title }}
                                                </td>
                                                <td>
                                                    {!! $place->active->getBadge() !!}
                                                </td>
                                                <td>{{ $place->user()->count() }}</td>
                                                <td>
                                                    @canany(['update', 'delete'], $place)
                                                        <div class="btn-group mt-2 mb-2">
                                                            <button type="button" class="btn btn-primary dropdown-toggle"
                                                                data-bs-toggle="dropdown">
                                                                عملیات <span class="caret"></span>
                                                            </button>
                                                            <ul class="dropdown-menu" role="menu">
                                                                @can('delete', $place)
                                                                    <li><a class="delete_confirm_alert" data-label="حذف "
                                                                            data-id="{{ $place->id }}" href="#">حذف</a>
                                                                    </li>
                                                                @endcan
                                                                @can('edit', $place)
                                                                    <li><a href="{{ route('admin.place.edit', ['place' => $place->id]) }}"
                                                                            data-label="ویرایش">ویرایش</a>
                                                                    </li>
                                                                @endcan
                                                            </ul>
                                                        </div>
                                                    @else
                                                        <div class="btn-group mt-2 mb-2">
                                                            <button type="button" class="btn btn-default dropdown-toggle"
                                                                data-bs-toggle="dropdown">
                                                                عملیات <span class="caret"></span>
                                                            </button>
                                                        </div>
                                                    @endcanany
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
                            {{ $places->links() }}
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
