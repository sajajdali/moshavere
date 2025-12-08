<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">مدیریت کاربران</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            @can('create', \Modules\User\Entities\User::class)
                <a href="{{ route('admin.user.create') }}" class="btn btn-azure">افزودن کاربر جدید</a>
            @endcan
        </div>
    </div>

    @include('admin::layouts.components.alert')

    <!-- Row -->
    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-bottom">
                    @can('user')
                        <h3 class="card-title">همه کاربران</h3>
                    @else
                        <h3 class="card-title">کاربران شما</h3>
                        @endif
                        <div class="card-options">
                            <div>
                                @empty(!$searchPanel)
                                    <button class="btn btn-secondary my-1" type="button" data-bs-toggle="collapse"
                                        wire:click='resetSearch' data-bs-target="#advanceSearch" aria-expanded="false"
                                        aria-controls="advanceSearch">
                                        نمایش همه
                                    </button>
                                @endempty
                                <button class="btn btn-primary" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#advanceSearch" aria-expanded="false" aria-controls="advanceSearch">
                                    جست و جوی پیشرفته
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- search collaps --}}
                        <div class="mb-5 collapse {{ $searchPanel }}" id="advanceSearch">
                            <form class="form-horizontal example">
                                {{-- user Info search --}}
                                <div class="row">
                                    <div class="col-12 col-md-3">
                                        <h4 class="text-center text-primary text-start ms-1">
                                            <a data-bs-toggle="collapse" href="#userDataCollaps" role="button"
                                                aria-expanded="false" aria-controls="userDataCollaps" href="">
                                                <i class="fa fa-user" aria-hidden="true"></i>
                                                <span>مشخصات کاربر</span>
                                            </a>
                                        </h4>
                                    </div>
                                    <div class=" col-12 col-md-9">
                                        <hr class="my-4">
                                    </div>
                                    <div class="collapse show row" id="userDataCollaps" wire:ignore.self>
                                        <div class="row mb-4">
                                            <label for="ID" class="col-md-2 form-label">ایدی</label>
                                            <div class="col-md-10">
                                                <input class="form-control" id="ID" wire:model="search.id"
                                                    placeholder="ایدی کاربر مورد نظر" type="text">
                                            </div>
                                        </div>
                                        <div class="row mb-4">
                                            <label for="email" class="col-md-2 form-label">ایمیل</label>
                                            <div class="col-md-10">
                                                <input class="form-control" id="email" wire:model="search.email"
                                                    placeholder="ایمیل کاربر مورد نظر" type="text">
                                            </div>
                                        </div>
                                        <div class="row mb-4">
                                            <label for="email" class="col-md-2 form-label">موبایل</label>
                                            <div class="col-md-10">
                                                <input class="form-control" id="email" wire:model="search.mobile"
                                                    placeholder="موبایل کاربر مورد نظر" type="text">
                                            </div>
                                        </div>
                                        <div class="row mb-4">
                                            <label for="first_name" class="col-md-2 form-label">نام</label>
                                            <div class="col-md-10">
                                                <input class="form-control" id="first_name" wire:model="search.first_name"
                                                    placeholder="نام کاربر مورد نظر" type="text">
                                            </div>
                                        </div>
                                        <div class="row mb-4">
                                            <label for="last_name" class="col-md-2 form-label">نام خانوادگی</label>
                                            <div class="col-md-10">
                                                <input class="form-control" id="last_name" wire:model="search.last_name"
                                                    placeholder="نام خانوادگی کاربر مورد نظر" type="text">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row my-4">
                                    <div class="col-12 col-md-3">
                                        <h4 class="text-center text-primary text-start ms-1">
                                            <a data-bs-toggle="collapse" href="#otherFilterSection" role="button"
                                                aria-expanded="false" aria-controls="otherFilterSection" href="">
                                                <i class="fa fa-user" aria-hidden="true"></i>
                                                <span>سایر فیلتر ها</span>
                                            </a>
                                        </h4>
                                    </div>
                                    <div class=" col-12 col-md-9">
                                        <hr class="my-4">
                                    </div>
                                    <div class="collapse row" id="otherFilterSection" wire:ignore.self>
                                        <div class="col-md-6">
                                            <label for="search-kind" class="form-label datePicker"><strong> نقش
                                                    کاربر</strong></label>
                                            <select class="form-control" id="search-kind" wire:model="search.role"
                                                type="text">
                                                <option value="">انتخاب کنید...</option>
                                                @foreach ($fetchData['roles'] as $role)
                                                    <option value="{{ $role->name }}">
                                                        {{ $role->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="search-drRegisteration" class="form-label datePicker"><strong> نوع
                                                    ثبت نام پزشک</strong></label>
                                            <select class="form-control" id="search-drRegisteration"
                                                wire:model="search.drRegisteration" type="text">
                                                <option value="">انتخاب کنید...</option>
                                                <option value="admin">ثبت شده توسط پنل ادمین</option>
                                                <option value="self">ثبت نام شده توسط خود پزشک</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="search-drActiveStatus" class="form-label datePicker"><strong>وضعیت
                                                    پزشک</strong></label>
                                            <select class="form-control" id="search-drActiveStatus"
                                                wire:model="search.drActiveStatus" type="text">
                                                <option value="">انتخاب کنید...</option>
                                                <option value="false">فعال</option>
                                                <option value="true">بن شده</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <button class="btn btn-primary" type="button" wire:click="startSearch"
                                    wire:loading.class="bg-gray btn-loading disabled" wire:click="updateOrCreate">جست و
                                    جو
                                </button>
                            </form>
                        </div>
                        <div class="table-responsive mb-3">
                            <table class="table text-nowrap text-md-nowrap table-bordered" wire:loading.class="op-0-3">
                                <thead>
                                    <tr class="text-center">
                                        <th scope="col">#</th>
                                        <th scope="col">نام کاربر</th>
                                        <th scope="col">موبایل</th>
                                        <th scope="col">نقش کاربر</th>
                                        <th scope="col">وضعیت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($users->isNotEmpty())
                                        @foreach ($users as $user)
                                            <tr class="text-center" wire:key="user_{{ $user->id }}">
                                                <td>{{ $user->id }}</td>
                                                <td>
                                                    <div class="media mt-4 profile-footer align-items-center text-start">
                                                        <div class="media-user me-2">
                                                            <div class="main-img-user"> <a
                                                                    href=" @can('documentte', $user){{ route('admin.user.document', $user) }} @else # @endcan">
                                                                    <img alt="{{ $user->full_name }}"
                                                                        title="{{ $user->full_name }}"
                                                                        class="rounded-circle avatar-md"
                                                                        src="{{ $user->getUserAvatar()}}">
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="media-body d-flex  justify-content-between align-items-center">
                                                            <a
                                                                href=" @can('documentte', $user){{ route('admin.user.document', $user) }} @else # @endcan">
                                                                <h6 class="mb-0">
                                                                    {{ $user->full_name }}
                                                                </h6>
                                                            </a>
                                                            @if($user->onlineAppointmentNewMessageCount() > 0 )
                                                            <a href="{{route('admin.appointment_user.message.detail',['onlineAppId' => $user->onlineAppIdforRoute()->id])}}" class="bg-red text-white p-2 rounded-pill small">
                                                                {{$user->onlineAppointmentNewMessageCount()}} پیام جدید
                                                            </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a
                                                        href=" @can('documentte', $user){{ route('admin.user.document', $user) }} @else # @endcan">
                                                        {{ $user->mobile }}
                                                    </a>
                                                </td>
                                                <td>
                                                    @if ($user->roles->isNotEmpty())
                                                        @foreach ($user->roles as $index => $role)
                                                            <span
                                                                class="badge {{ $user->getUserBadge() }}">{{ $role->name ?? 'کاربر' }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="badge bg-secondary">کاربر</span>
                                                    @endif
                                                    @if ($user->IsDoctor())
                                                        @if ($user->active_appointment == '0')
                                                            <span class="badge bg-danger">غیر فعال</span>
                                                        @else
                                                            <span class="badge bg-success">فعال</span>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>
                                                    @canany(['update', 'delete'], $user)
                                                        <div class="btn-group mt-2 mb-2">
                                                            <button type="button" class="btn btn-primary dropdown-toggle"
                                                                data-bs-toggle="dropdown">
                                                                عملیات <span class="caret"></span>
                                                            </button>
                                                            <ul class="dropdown-menu" role="menu">
                                                                @can('update', $user)
                                                                    <li>
                                                                        @unless ($user->id === 1)
                                                                        <a href="{{ route('admin.user.edit', $user) }}">ویرایش</a>
                                                                        @endunless
                                                                    </li>
                                                                    @if ($user->hasrole('پزشک'))
                                                                        <li>
                                                                            <a href="{{ route('admin.doctor.info', $user) }}">ویرایش
                                                                                اطلاعات پزشک</a>
                                                                        </li>
                                                                        <li>
                                                                            <a href="{{ route('admin.doctor.gallery', $user) }}">
                                                                                گالری پزشک</a>
                                                                        </li>
                                                                    @endif
                                                                @endcan
                                                                @can('documentte', $user)
                                                                    @if ($user->hasRole('بیمار'))
                                                                        <li>
                                                                            <a href="{{ route('admin.user.document', $user) }}">مشاهده
                                                                                پرونده</a>
                                                                        </li>
                                                                    @endif
                                                                @endcan
                                                                @if ($user->hasRole('اپراتور'))
                                                                    <li>
                                                                        <a
                                                                            href="{{ route('admin.oprator.timesetting', $user) }}">تنظیمات
                                                                            زمان حضور اپراتور</a>
                                                                    </li>
                                                                @endif
                                                                @can('delete', $user)
                                                                    @unless ($user->id === 1)
                                                                        <li><a class="delete_confirm_alert" data-label="کاربر"
                                                                                data-id="{{ $user->id }}" href="">حذف</a>
                                                                        </li>
                                                                    @else
                                                                        <li><a class="admin_sweet_alert op-0-4" data-title="خطا"
                                                                                data-type="error"
                                                                                data-description="امکان حذف این کاربر وجود ندارد."
                                                                                href="#">حذف</a>
                                                                        </li>
                                                                    @endunless
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
                                            <td colspan="4" class="text-center">
                                                <div class="alert alert-info">
                                                    هیچ کاربری یافت نشد
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div>
                            {{ $users->links() }}
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
