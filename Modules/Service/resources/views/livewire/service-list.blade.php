<div>

    <div>
        <div class="page-header">
            <div>
                <h1 class="page-title">لیست بخش ها</h1>
            </div>
            <div class="ms-auto pageheader-btn">
                @can('create', Modules\Service\app\Models\Service::class)
                    <a href="{{ route('admin.service.create') }}" class="btn btn-info">افزودن بخش جدید</a>
                @endcan
            </div>
        </div>
        @include('admin::layouts.components.alert')
        <div class="row row-sm">
            <div class="col-lg-12">
                <div class="card custom-card">
                    <div class="card-header d-flex justify-content-between border-bottom">
                        <h3 class="card-title">مدیریت بخش ها</h3>
                        <div class="card-options">
                            <button class="btn btn-primary" type="button" data-bs-toggle="collapse"
                                data-bs-target="#advanceSearch" aria-expanded="false" aria-controls="advanceSearch">
                                جست و جوی پیشرفته
                            </button>
                            @if (isset($search['id']) || isset($search['specialityName']) || isset($search['status']))
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
                                            placeholder="ایدی بخش مورد نظر" type="text">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <label for="search-name" class="col-md-2 form-label">نام بخش</label>
                                    <div class="col-md-10">
                                        <input class="form-control" id="search-name" wire:model="search.title"
                                            placeholder="نام بخش" type="text">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <label class="form-label col-md-2" for="default-dropdown">وضعیت</label>
                                    <div class="col-md-10">
                                        <select wire:model='search.active' name="country"
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
                                        <th scope="col">نام بخش</th>
                                        <th scope="col">وضعیت</th>
                                        <th scope="col">زیربخش</th>
                                        <th scope="col">تعداد پزشکان</th>
                                        <th scope="col">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($services->isNotEmpty())
                                        @foreach ($services as $service)
                                            @if (!empty($service->parent_id))
                                                @continue
                                            @endif
                                            <tr class="text-center">
                                                <td>{{ $service->id }}</td>
                                                <td>
                                                    @if ($service->subSection()?->count() != 0)
                                                        <a wire:click='passModalData({{ $service->id }})'
                                                            href="" type="button" data-bs-toggle="modal"
                                                            data-bs-target="#staticBackdrop">
                                                            <i class="fa fa-plus-square" aria-hidden="true"></i>
                                                            {{ $service->title }}
                                                        </a>
                                                    @else
                                                        {{ $service->title }}
                                                    @endif

                                                </td>
                                                <td>
                                                    {!! $service->active->getBadge() !!}
                                                </td>
                                                <td> <span
                                                        class="badge bg-secondary p-3">{{ $service->subSection()?->count() ?? 0 }}</span>
                                                </td>
                                                <td>{{ $service->user?->count() ?? 0 }} </td>
                                                <td>
                                                    @canany(['update', 'delete'], $service)
                                                        <div class="btn-group mt-2 mb-2">
                                                            <button type="button" class="btn btn-primary dropdown-toggle"
                                                                data-bs-toggle="dropdown">
                                                                عملیات <span class="caret"></span>
                                                            </button>
                                                            <ul class="dropdown-menu" role="menu">
                                                                @can('delete', $service)
                                                                    <li><a class="delete_confirm_alert" data-label="حذف "
                                                                            data-id="{{ $service->id }}" href="#">حذف</a>
                                                                    </li>
                                                                @endcan
                                                                @can('update', $service)
                                                                    <li><a href="{{ route('admin.service.edit', ['service' => $service]) }}"
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
                            {{-- {{ $specialities->links() }} --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('service::components.subsectionmodal')
</div>
@push('scripts')
    <script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/admin.sweetalert.js') }}"></script>

    <script>
        $('.click-section').click(function(e) {
            // Find the plus and minus icons within the clicked div
            var plusIcon = $(this).find('.plus');
            var minusIcon = $(this).find('.minus');

            // Toggle the visibility of the icons
            plusIcon.toggleClass('d-none');
            minusIcon.toggleClass('d-none');
        });
    </script>
@endpush
