<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">پزشکان</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            @can('create', \Modules\User\Entities\User::class)
                <a href="{{ route('admin.user.create') }}" class="btn btn-azure">افزودن کاربر جدید</a>
            @endcan
        </div>
    </div>

    @include('admin::layouts.components.alert')

    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">لیست پزشکان</h3>
                    <div class="card-options">
                        <input class="form-control" style="min-width:260px" type="text"
                            wire:model.live.debounce.500ms="search" placeholder="جست و جو: نام، موبایل یا آیدی">
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive mb-3">
                        <table class="table text-nowrap text-md-nowrap table-bordered" wire:loading.class="op-0-3">
                            <thead>
                                <tr class="text-center">
                                    <th scope="col">#</th>
                                    <th scope="col">نام پزشک</th>
                                    <th scope="col">موبایل</th>
                                    <th scope="col">بخش‌ها</th>
                                    <th scope="col">نوبت‌دهی</th>
                                    <th scope="col">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($doctors as $doctor)
                                    <tr class="text-center" wire:key="doctor_{{ $doctor->id }}">
                                        <td>{{ $doctor->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2 text-start">
                                                <img alt="{{ $doctor->full_name }}" class="rounded-circle avatar-md" src="{{ $doctor->getUserAvatar() }}">
                                                <div>
                                                    <h6 class="mb-0">{{ $doctor->full_name }}</h6>
                                                    @if ($doctor->DocSpecialities())
                                                        <small class="text-muted">{{ $doctor->DocSpecialities() }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td dir="ltr">{{ $doctor->mobile }}</td>
                                        <td class="text-wrap" style="max-width:260px">
                                            @forelse ($doctor->services as $service)
                                                <span class="badge bg-primary-transparent text-primary mb-1">{{ $service->title }}</span>
                                            @empty
                                                <span class="text-muted">—</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            @if ($doctor->active_appointment == '0')
                                                <span class="badge bg-danger">غیر فعال</span>
                                            @else
                                                <span class="badge bg-success">فعال</span>
                                            @endif
                                        </td>
                                        <td>
                                            @can('update', $doctor)
                                                <div class="btn-group mt-2 mb-2">
                                                    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                        عملیات <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu" role="menu">
                                                        <li>
                                                            <a href="#" wire:click.prevent="$dispatch('open-doctor-service-links', { doctorId: {{ $doctor->id }} })">
                                                                <i class="fa fa-link me-1"></i> لینک خدمت مشخص</a>
                                                        </li>
                                                        <li><a href="{{ route('admin.user.edit', $doctor) }}"><i class="fa fa-pencil me-1"></i> ویرایش</a></li>
                                                        <li><a href="{{ route('admin.doctor.info', $doctor) }}"><i class="fa fa-id-card-o me-1"></i> ویرایش اطلاعات پزشک</a></li>                                                    </ul>
                                                </div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <div class="alert alert-info mb-0">هیچ پزشکی یافت نشد</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div>
                        {{ $doctors->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <livewire:user::admin.user.doctor-service-links />
</div>
