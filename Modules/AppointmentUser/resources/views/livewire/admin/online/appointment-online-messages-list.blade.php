<div>
    <div wire:loading>
        <div class="loading-overlay d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>
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

                            </div>
                        </div>
                        {{-- search inputs --}}
                        <div class="card-body d-flex py-2">
                            <div class="collapse w-100  @foreach ($search as $key => $value)
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
                                                isset($search['appointment_set_date']) ) show @endif"
                                            id="appointmentCollapsSearch" wire:ignore.self>
                                            <div class="col-md-6">
                                                <label for="search-appointment_date" class="form-label"><strong>زمان
                                                        نوبت</strong></label>
                                                <input class="form-control" id="search-appointment_date"
                                                    wire:model="search.appointment_date"
                                                    placeholder="زمانی که نوبت دریافت شده" type="text">

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
                                    <div class="row my-5">
                                        <div class="col-12 col-md-3">
                                            <h4 class="text-center text-primary text-start ms-1"><a
                                                    data-bs-toggle="collapse" href="#appointmentMessageSearch"
                                                    role="button" aria-expanded="false"
                                                    aria-controls="appointmentMessageSearch">
                                                    <i class="fa fa-calendar-check-o" aria-hidden="true"></i>
                                                    <span>پیام ها</span>
                                                </a></h4>
                                        </div>
                                        <div class="col-12 col-md-9">
                                            <hr class="my-4">
                                        </div>
                                        <div class="collapse row
                                        @if (isset($search['appointment_messages'])) show @endif"
                                            id="appointmentMessageSearch" wire:ignore.self>
                                            <div class="col-md-12">
                                                <label for="search-appointment_messages"
                                                    class="form-label"><strong>متن پیام</strong></label>
                                                <input class="form-control" id="search-appointment_messages"
                                                    wire:model="search.appointment_messages"
                                                    placeholder="متن پیامی که ارسال شده است" type="text">

                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary" type="button" wire:click="startSearch"
                                        wire:loading.class="bg-gray btn-loading disabled">جست و
                                        جو
                                    </button>
                                    @foreach ($search as $key => $value)
                                        @if ($value !== null)
                                            <button class="btn btn-secondary ms-2" wire:click="resetProperties"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#advanceSearch" aria-expanded="false"
                                                aria-controls="advanceSearch"
                                                wire:loading.class="bg-gray btn-loading disabled">نمایش
                                                همه
                                            </button>
                                        @break
                                    @endif
                                @endforeach
                            </form>
                        </div>
                    </div>
                    {{-- chats --}}
                    @foreach ($this->handleSearch() as $message)
                        <div class="card border-0 shadow rounded-lg mb-4" style="background-color: #f1f1f1">
                            <div
                                class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center p-3">
                                <div class="d-flex flex-column">
                                    <span class="text-muted">{{ $message->id }}</span>
                                    <a class="fw-bold ms-2 mt-2 h5 mb-0" href="{{ route('admin.appointment_user.message.detail', ['onlineAppId' => $message->online->id]) }}" >{{ $message->user->full_name }}</a>
                                </div>
                                <div class="text-end">
                                    <span
                                        class="text-muted small d-block mt-1">{{ ($message->updated_at)->diffForHumans() }}
                                         </span>
                                </div>
                            </div>
                            <div class="card-body d-flex justify-content-between align-items-center p-3">
                                <a class="btn btn-primary rounded-full"
                                    href="{{ route('admin.appointment_user.message.detail', ['onlineAppId' => $message->online->id]) }}">
                                    {{ $message->countUserMessages() }} پیام
                                </a>
                                @if ($message->online->status->isPendding())
                                    <div>
                                        <button wire:click='ApproveOnlineAppointment("{{ $message->online->appointmentUser->id}}")' class="btn btn-success rounded-pill px-4 py-2 me-2 loading-btn">
                                            <i class="fa fa-check me-2" aria-hidden="true"></i> تایید نوبت
                                        </button>
                                        <button wire:click='disApproveOnlineAppointment("{{ $message->online->appointmentUser->id}}")' class="btn btn-danger rounded-pill px-4 py-2">
                                            <i class="fa fa-times me-2" aria-hidden="true"></i> رد کردن
                                        </button>
                                    </div>
                                @else
                                    <div>
                                        {!! $message->online->status->getMessageDetailBadge() !!}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                </div>
                <div class="d-flex justify-content-center mb-5">
                    {{-- {{$this->handleSearch()->links()}} --}}
                </div>
            </div>
        </div>
    </div>
    @include('appointmentuser::components.appointmentlist.disapprovemodal')
</div>
@push('scripts')
    <script>
        $(document).ready(function() {
            function js() {
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
            Livewire.on('lunchModal', function() {
            setTimeout(() => {
                var myModal = new bootstrap.Modal(document.getElementById(
                    'resoanForDisapproveModal'), {
                    keyboard: false
                });
                myModal.show();
            }, 1000);
        });
        });
    </script>
@endpush
