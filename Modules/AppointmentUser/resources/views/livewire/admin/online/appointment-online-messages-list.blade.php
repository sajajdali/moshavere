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
                <li class="breadcrumb-item"><a href="javascript:void(0);">مدیریت</a></li>
                <li class="breadcrumb-item active" aria-current="page">چت ها</li>
            </ol>
        </div>

    </div>
    <!-- PAGE-HEADER END -->
    @error('msgerror')
        <div class="col-md-12 alert alert-danger fade show" role="alert">
            <i class="fa fa-remove me-2" aria-hidden="true"></i>
            {{ $message }}
        </div>
    @enderror

    @include('admin::layouts.components.alert')

    <div class="row">
        <div class="card overflow-scroll">
            <div class="main-content-app pt-0">
                <div class="main-content-left main-content-left-chat">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between border-bottom px-1 px-sm-3">
                            <div>
                                <h3 class="card-title">لیست نوبت های ثبت شده</h3>
                                <span class="badge bg-light rounded-pill mt-1">
                                    {{ \Modules\AppointmentUser\app\Models\AppointmentOnlineMessage::totalUnreaedMessage() }}
                                    نوبت بدون پاسخ
                                </span>
                            </div>
                            @if (auth()->user()->isMama())
                                <div class="d-flex justify-content-around">
                                    <button wire:click='showStatus("all")' disabled
                                        class="btn    @if ($show == 'all') btn-success   @else btn-info @endif">نمایش
                                        همه</button>
                                    <button wire:click='showStatus("mine")' disabled
                                        class="btn    @if ($show == 'mine') btn-success  @else btn-info @endif mx-2">نوبت
                                        های من</button>
                                    <button wire:click='showStatus("empty")' disabled
                                        class="btn    @if ($show == 'empty') btn-success @else btn-info @endif">نوبت
                                        های خالی</button>
                                </div>
                            @endif
                            <div class="flex-column flex-sm-row">
                                <button class="btn btn-warning me-2" type="button" wire:click='showalltheMessages'>
                                    نمایش نوبت های تمام شده
                                </button>
                                <button class="btn btn-primary mt-1 mt-md-0 " type="button" data-bs-toggle="collapse"
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
                                            <div class="col-12">
                                                <label for="search-UserMobile" class="form-label"><strong>کد
                                                        ملی</strong></label>
                                                <input class="form-control" id="search-UserMobile"
                                                    wire:model="search.nationalCode" placeholder="کد ملی بیمار"
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
                                        @if (isset($search['appointment_date']) || isset($search['appointment_set_date'])) show @endif"
                                            id="appointmentCollapsSearch" wire:ignore.self>
                                            <div class="col-md-6">
                                                <label for="search-appointment_date" class="form-label"><strong>زمان
                                                        نوبت</strong></label>
                                                <input class="form-control" id="search-appointment_date" data-jdp data-name="search.appointment_date"
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
                            @php
                                $color = 'f1f1f1';
                                if (
                                    $message->online->status ==
                                        \Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum::REJECT ||
                                    $message->online->status ==
                                        \Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum::CANCEL
                                ) {
                                    $color = 'ffcaca';
                                }
                                if (
                                    $message->online->status ==
                                    \Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum::COMPLETED_BY_DOCTOR
                                ) {
                                    $color = 'f9f6cf';
                                }
                            @endphp
                            <div class="card border-0 shadow rounded-lg mb-4"
                                style="background-color: #{{ $color }}">
                                <div
                                    class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center p-3">
                                    <div class="d-flex flex-column absoloute">
                                        <span class="text-muted">{{ $loop->count - $loop->index }}</span>
                                        <a target="blank" class="fw-bold ms-2 mt-2 h5 mb-0"
                                            href="{{ route('admin.appointment_user.message.detail', ['onlineAppId' => $message->online->id]) }}">{{ $message->user->full_name }}</a>
                                    </div>
                                    <div class="text-end">
                                        <span class=" badge bg-light small d-block mt-1  px-1"><strong>اخرین
                                                پیام</strong>: {{ $message->updated_at->diffForHumans() }}
                                        </span>
                                        <span class="mt-1 d-block bg-light bg-light">
                                            <span class="d-flex flex-column flex-md-row text-center  px-2">
                                                <strong>زمان دریافت نوبت:</strong>
                                                <span>{{ verta($message->online->created_at)->format('Y/m/d ساعت H:i') }}</span>
                                            </span>
                                        </span>
                                        @if ($message->hasAnswer())
                                            <span class="mt-1 d-block bg-light bg-light text-center px-1"><strong>پاسخ
                                                    توسط</strong>: {{ $message->findAwnswerer() }} </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body d-flex justify-content-between align-items-center p-3">
                                    <a target="blank"
                                        class="btn  @if ($message->unReadedMessageCount() > 0) btn-secondary @else  btn-primary @endif rounded-full"
                                        href="{{ route('admin.appointment_user.message.detail', ['onlineAppId' => $message->online->id]) }}">
                                        @if ($message->unReadedMessageCount() > 0)
                                            {{ $message->unReadedMessageCount() }} پیام
                                        @else
                                            بدون پیام جدید
                                        @endif
                                    </a>

                                    <div class="d-flex flex-column align-items-end">
                                        @if ($message->online->status->isPendding())
                                            <button
                                                wire:click='ApproveOnlineAppointment("{{ $message->online->appointmentUser->id }}")'
                                                class="btn btn-success rounded-pill px-4 py-2 me-2 loading-btn">
                                                <i class="fa fa-check me-2" aria-hidden="true"></i> تایید نوبت
                                            </button>
                                            <button
                                                wire:click='disApproveOnlineAppointment("{{ $message->online->appointmentUser->id }}")'
                                                class="btn btn-danger rounded-pill px-4 py-2">
                                                <i class="fa fa-times me-2" aria-hidden="true"></i> رد کردن
                                            </button>
                                        @else
                                            {!! $message->online->status->getMessageDetailBadge() !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-center mb-5">
                        {{ $this->handleSearch()->links() }}
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
                    jalaliDatepicker.startWatch({
                        zIndex: 99999
                    });
                    $(document).on('input', '[data-jdp]', function() {
                        let selectedDate = $(this).val();
                        let seterValue = $(this).data('name');
                        @this.set(seterValue, selectedDate);
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
