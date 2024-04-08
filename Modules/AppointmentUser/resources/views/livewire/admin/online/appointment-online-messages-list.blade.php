<div>
    <!-- PAGE-HEADER -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Chat</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Apps</a></li>
                <li class="breadcrumb-item active" aria-current="page">Chat</li>
            </ol>
        </div>

    </div>
    <!-- PAGE-HEADER END -->

    @include('admin::layouts.components.alert')

    <div class="row">
        <div class="card overflow-scroll">
            <div class="main-content-app pt-0">
                <div class="main-content-left main-content-left-chat">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between border-bottom">
                            <h3 class="card-title">لیست نوبت های ثبت شده</h3>
                            <div class="card-options">
                                <button class="btn btn-primary" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#advanceSearch" aria-expanded="false" aria-controls="advanceSearch">
                                    جست و جوی پیشرفته
                                </button>
                                @foreach ($search as $key => $value)
                                    @if ($value !== null)
                                        <button class="btn btn-secondary ms-2" wire:click="resetProperties"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#advanceSearch"
                                            aria-expanded="false" aria-controls="advanceSearch"
                                            wire:loading.class="bg-gray btn-loading disabled">نمایش
                                            همه
                                        </button>
                                    @break
                                @endif
                            @endforeach
                        </div>
                    </div>
                    {{-- search inputs --}}
                    <div class="card-body d-flex py-2">
                        <div class="collapse  @foreach ($search as $key => $value)
                            @if ($value !== null) show @break @endif @endforeach "
                            id="advanceSearch" wire:ignore.self>
                            <form class="form-horizontal example" autocomplete="off">
                                <div class="row mb-5">
                                    <div class="col-12 col-md-3">
                                        <h4 class="text-center text-primary text-start ms-1"><a
                                                data-bs-toggle="collapse" href="#userDataCollaps" role="button"
                                                aria-expanded="false" aria-controls="userDataCollaps"
                                                href="">
                                                <i class="fa fa-user" aria-hidden="true"></i>
                                                <span>مشخصات کاربر</span>
                                            </a></h4>
                                    </div>
                                    <div class=" col-12 col-md-9">
                                        <hr class="my-4">
                                    </div>
                                    <div class=" col-12 collapse show row" id="userDataCollaps">
                                        <div class="col-md-6 form-group">
                                            <label for="search-id" class=" form-label"><strong>ایدی</strong></label>
                                            <input class="form-control" id="search-id" wire:model="search.user_id"
                                                placeholder="ایدی کاربر مورد نظر" type="text">

                                        </div>
                                        <div class="col-md-6">
                                            <label for="search-Username"
                                                class="form-label"><strong>نام</strong></label>
                                            <input class="form-control" id="search-Username"
                                                wire:model="search.user_first_name" placeholder="نام کاربر مورد نظر"
                                                type="text">

                                        </div>
                                        <div class="col-md-6">
                                            <label for="search-UserLname" class="form-label"><strong>نام
                                                    خانوادگی</strong></label>
                                            <input class="form-control" id="search-UserLname"
                                                wire:model="search.user_last_name"
                                                placeholder="نام خانوادگی کاربر مورد نظر" type="text">

                                        </div>
                                        <div class="col-md-6">
                                            <label for="search-UserMobile" class="form-label"><strong>شماره
                                                    موبایل</strong></label>
                                            <input class="form-control" id="search-UserMobile"
                                                wire:model="search.user_mobile" placeholder="شماره تماس"
                                                type="text">

                                        </div>
                                    </div>
                                </div>
                                <div class="row my-5">
                                    <div class="col-12 col-md-3">
                                        <h4 class="text-center text-primary text-start ms-1"><a
                                                data-bs-toggle="collapse" href="#appointmentCollapsSearch"
                                                role="button" aria-expanded="false"
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
                                    isset($search['appointment_star_date']) ||
                                    isset($search['appointment_end_date'])) show @endif"
                                        id="appointmentCollapsSearch" wire:ignore.self>
                                        <div class="col-md-6">
                                            <label for="search-appointment_date" class="form-label"><strong>زمان
                                                    نوبت</strong></label>
                                            <input class="form-control" id="search-appointment_date"
                                                wire:model="search.appointment_date"
                                                placeholder="زمانی که نوبت دریافت شده" type="text">

                                        </div>
                                        <div class="col-md-6">
                                            <label for="search-id-appointment_set_date"
                                                class="form-label"><strong>زمان
                                                    ثبت
                                                    نوبت</strong></label>
                                            <input class="form-control" id="search-appointment_set_date"
                                                wire:model="search.appointment_set_date"
                                                placeholder="زمانی که نوبت ثبت شده" type="text">

                                        </div>
                                        <div class="col-md-6">
                                            <label for="search-id-appointment_star_date"
                                                class="form-label"><strong>تاریخ
                                                    شروع</strong></label>
                                            <input class="form-control" id="search-appointment_star_date"
                                                wire:model="search.appointment_star_date"
                                                placeholder="نوبت های از این تاریخ به بعد" type="text">

                                        </div>
                                        <div class="col-md-6">
                                            <label for="search-id-appointment_end_date"
                                                class="form-label"><strong>تاریخ
                                                    پایان</strong></label>
                                            <input class="form-control" id="search-appointment_end_date"
                                                wire:model="search.appointment_end_date"
                                                placeholder="نوبت هایی ازین تاریخ به قبل" type="text">

                                        </div>
                                        <div class="col-md-6">
                                            <label for="search-appStatusId"
                                                class="form-label datePicker"><strong>وضعیت
                                                    نوبت</strong></label>
                                            <select class="form-control" id="search-appStatusId"
                                                wire:model="search.AppointmentStatus" placeholder="نام ثبت نوبت"
                                                type="text">
                                                <option value="">انتخاب کنید...</option>
                                                @foreach (Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum::cases() as $enum)
                                                    <option value="{{ $enum->value }}">
                                                        {{ $enum->getName() }}

                                                    </option>
                                                @endforeach
                                            </select>

                                        </div>
                                        <div class="col-md-6">
                                            <label for="search-docNumberId" class="form-label"><strong>شماره
                                                    پرونده</strong></label>
                                            <input class="form-control" id="search-docNumberId"
                                                wire:model="search.docNumber" placeholder="ایدی رژیم مورد نظر"
                                                type="text">

                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-primary" type="button" wire:click="startSearch"
                                    wire:loading.class="bg-gray btn-loading disabled">جست و
                                    جو
                                </button>
                            </form>
                        </div>
                    </div>
                    {{-- filter tab --}}
                    <div class="tab-menu-heading border-0">
                        <div class="tabs-menu">
                            <ul class="nav panel-tabs">
                                <li><a href="#ChatList" class="me-2 active mb-2" data-bs-toggle="tab">پیام های
                                        پاسخ داده نشده</a>
                                </li>
                                <li><a href="#ChatGroups" class="me-2 mb-2" data-bs-toggle="tab">پیام های پاسخ
                                        داده شده</a></li>
                            </ul>
                        </div>
                    </div>
                    {{-- chats --}}
                    <div class="tab-content main-chat-list flex-2" wire:loading.class='opacity-50'>
                        <div class="tab-pane active" id="ChatList">
                            <div class="main-chat-list tab-pane">
                                @foreach ($this->handleSearch() as $messages)
                                    <a class="media new" href="#">
                                        <div class="main-img-user">
                                            <img alt="" src="{{ asset('assets/images/users/6.jpg') }}">
                                            <span>3</span>
                                        </div>
                                        <div class="media-body">
                                            <div class="media-contact-name">
                                                <span>Ariana Monino</span> <span>30 min</span>
                                            </div>
                                            <p>Good Morning</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            <!-- main-chat-list -->
                        </div>
                        <div class="tab-pane" id="ChatGroups">
                            <a class="media new" href="#">
                                <div class="main-img-user">
                                    <img alt="" src="{{ asset('assets/images/users/6.jpg') }}">
                                    <span>3</span>
                                </div>
                                <div class="media-body">
                                    <div class="media-contact-name">
                                        <span>Ariana Monino</span> <span>30 min</span>
                                    </div>
                                    <p>Good Morning</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        $(document).ready(function() {
            function js() {
                $('.select2-show-search').select2();
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
            })
        });
    </script>
@endpush
