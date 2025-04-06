<div>
    <div class="page-header mb-5">
        <div>
            <h1 class="page-title">تنظیمات زمان های حضور</h1>
        </div>
    </div>
    @include('admin::layouts.components.alert')
    <div class="card">
        <div class="card-header border-bottom">
            <h3 class="card-title"> انتخاب پزشک</h3>
            <div class="card-options">
                <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#advanceSearch"
                    aria-expanded="false" aria-controls="advanceSearch">
                    جست و جوی پیشرفته
                </button>
                @if (isset($search['id']) || isset($search['mobile']) || isset($search['first_name']) || isset($search['last_name']))
                    <button class="btn btn-secondary ms-2" type="button" wire:click="resetProperties"
                        wire:loading.class="bg-gray btn-loading disabled">نمایش همه
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if (! isset($doctors))
            <div class="col-md-12 alert alert-primary fade show" role="alert">
                <i class="fa fa-bell-o me-2 ms-1" aria-hidden="true"></i>
                پزشکی یافت نشد ! لطفا ابتدا پزشک به سیستم اضافه کنید .
            </div>
            @endif
            <div class="mb-5 collapse {{ $searchPanel }}" id="advanceSearch" wire:ignore>
                <form class="form-horizontal example" autocomplete="off">
                    <div class="row mb-4">
                        <label for="search-id" class="col-md-2 form-label">ایدی</label>
                        <div class="col-md-10">
                            <input class="form-control" id="search-id" wire:model="search.id" placeholder="ایدی پزشک"
                                type="text">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="search-name" class="col-md-2 form-label">نام پزشک</label>
                        <div class="col-md-10">
                            <input class="form-control" id="search-name" wire:model="search.first_name"
                                placeholder="نام " type="text">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="search-name" class="col-md-2 form-label">نام خانوادگی پزشک</label>
                        <div class="col-md-10">
                            <input class="form-control" id="search-name" wire:model="search.last_name"
                                placeholder="نام خانوادگی" type="text">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="search-name" class="col-md-2 form-label">موبایل پزشک</label>
                        <div class="col-md-10">
                            <input class="form-control" id="search-name" wire:model="search.mobile"
                                placeholder="شماره تماس" type="text">
                        </div>
                    </div>

                    <button class="btn btn-primary" type="button" wire:click="startSearch"
                        wire:loading.class="bg-gray btn-loading disabled">جست و
                        جو
                    </button>
                </form>
            </div>
            @if (isset($doctors) && $form['services'] != 'true' && $form['place'] != 'true')
                <div class="row mt-5">
                    <div class="row">
                        <h5 class="text-muted mt-1 mb-5">برای تنظیم زمان حضور، پزشک مورد نظر را انتخاب کنید</h5>
                        @foreach ($doctors as $key => $doctor)
                            <div class="col-lg-6 col-md-12 col-sm-12">
                                <div class="card mb-5 shadow-lg" style="border-radius: 10px">
                                    <div class="card-body">
                                        <div class="client-title mt-0 flex-column flex-sm-row">
                                            <figure class="rounded-circle align-self-start mb-0">
                                                @if ($doctor->avatar)
                                                    <img src="{{ $doctor->avatar }}" alt="Generic placeholder image"
                                                        class="avatar brround avatar-lg me-3">
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-inner-icn"
                                                        enable-background="new 0 0 24 24" viewBox="0 0 24 24">
                                                        <path
                                                            d="M14.6650391,13.3672485C16.6381226,12.3842773,17.9974365,10.3535767,18,8c0-3.3137207-2.6862793-6-6-6S6,4.6862793,6,8c0,2.3545532,1.3595581,4.3865967,3.3334961,5.3690186c-3.6583862,1.0119019-6.5859375,4.0562134-7.2387695,8.0479736c-0.0002441,0.0013428-0.0004272,0.0026855-0.0006714,0.0040283c-0.0447388,0.272583,0.1399536,0.5297852,0.4125366,0.5745239c0.272522,0.0446777,0.5297241-0.1400146,0.5744629-0.4125366c0.624939-3.8344727,3.6308594-6.8403931,7.465332-7.465332c4.9257812-0.8027954,9.5697632,2.5395508,10.3725586,7.465332C20.9594727,21.8233643,21.1673584,21.9995117,21.4111328,22c0.0281372,0.0001831,0.0562134-0.0021362,0.0839844-0.0068359h0.0001831c0.2723389-0.0458984,0.4558716-0.303833,0.4099731-0.5761719C21.2677002,17.5184937,18.411377,14.3986206,14.6650391,13.3672485z M12,13c-2.7614136,0-5-2.2385864-5-5s2.2385864-5,5-5c2.7600708,0.0032349,4.9967651,2.2399292,5,5C17,10.7614136,14.7614136,13,12,13z" />
                                                    </svg>
                                                @endif
                                            </figure>
                                            <div class="media-body my-3 my-sm-0">
                                                <h4 class="time-title p-0 mb-0 font-weight-semibold leading-normal">
                                                    <a href="{{ route('admin.appointment.setting', ['user' => $doctor->id]) }}"
                                                        class="text-dark">{{ $doctor->fullName }}</a>
                                                </h4>
                                                <span></span>
                                            </div>
                                            <a href="{{ route('admin.appointment.setting', ['user' => $doctor->id]) }}"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="تنظمات روز های حضور" class="btn btn-info d-block loading-btn">
                                                <i class="fa fa-calendar"aria-hidden="true"></i> <span>تنظیمات روز های
                                                    حضور</span>
                                            </a>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-center mt-4">
                                            <div
                                                class="pe-4 border-end d-flex align-items-center justify-content-center">
                                                <h5 class="mb-0 me-3 text-muted">بخش ها</h5>
                                                <p class="m-0 text-dark">{{$doctor->service->count()}}</p>
                                            </div>
                                            <div class="ms-4 d-flex align-items-center justify-content-center">
                                                <h5 class="mb-0 me-3 text-muted">بخش با زمان اختصاصی</h5>
                                                <p class="m-0 text-dark">{{$doctor->specialServiceseCount()}}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="d-flex justify-content-center">
                            {{$doctors->links()}}
                        </div>
                    </div>
                </div>
            @elseif($form['services'])
                <div class="alert alert-primary alert-dismissible fade show" role="alert">
                    <span class="alert-inner--text"><strong>بخشی یافت نشد!!</strong>
                        <br>
                        لطفا ابتدا بخش به سیستم اضافه کنید

                        اضافه کنید</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                @can('create', \Modules\Service\app\Models\Service::class)
                <a href="{{ route('admin.service.create') }}" class="btn btn-success">افزودن بخش جدید
                </a>
                @endcan
            @elseif($form['place'])
                <div class="alert alert-primary alert-dismissible fade show" role="alert">
                    <span class="alert-inner--text"><strong>مطب یافت نشد!!</strong>
                        <br>
                        لطفا ابتدا مطب به سیستم اضافه کنید

                        اضافه کنید</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <a href="{{ route('admin.place.create') }}" class="btn btn-success">افزودن مطب جدید
                </a>
            @elseif(isset($doctors) &&  $doctors->count() < 1)
                <div class="alert alert-primary alert-dismissible fade show" role="alert">
                    <span class="alert-inner--text"><strong>پزشکی یافت نشد!!</strong>
                        <br>
                        لطفا ابتدا پزشکان را به سایت

                        اضافه کنید</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <a href="{{ route('admin.user.create') }}" class="btn btn-success">افزودن پزشک جدید
                </a>
            <div class="d-flex justify-content-center">
                  {{ $doctors->links() }}
            </div>
            @endif
        </div>
    </div>

</div>
