<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">
                پرونده کاربر
            </h1>
        </div>
    </div>

    @include('admin::layouts.components.alert')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title">پرونده کاربر</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- seperator --}}
                        <div class="col-12 row mb-3">
                            <div class="col-md-3">
                                <h5 class="text-info">
                                    <i class="fa fa-user me-1" aria-hidden="true"></i>
                                    مشخصات کاربر
                                </h5>
                            </div>
                            <div class="col-md-9">
                                <hr>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="table-responsive text-center">
                                <table class="table border text-nowrap text-md-nowrap table-striped">
                                    <thead>
                                        <tr>
                                            <th>نام</th>
                                            <th>موبایل</th>
                                            <th>کد ملی</th>
                                            <th>شماره پرونده</th>
                                            <th>ایمیل</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ $user->fullName ?? 'ثبت نشده است' }}</td>
                                            <td x-data="{ edit: false, value: '{{ $user->mobile }}' }">
                                                <div class="d-flex  align-items-center justify-content-center gap-1">
                                                    <input type="text" class="form-control form-control-sm"
                                                        x-show="edit" x-cloak x-model="value"
                                                        style="width: 110px" />
                                                    <span x-show="!edit" x-text="value || 'ثبت نشده'"
                                                        style="width: 110px"></span>

                                                    <div class="d-flex gap-2">
                                                        <a type="button" @click="edit = !edit" title="ویرایش">
                                                            <i class="fa fa-pencil-square-o fs-5"></i>
                                                        </a>

                                                        <a type="button" x-show="edit" x-cloak class="text-success"
                                                            title="ذخیره"
                                                            @click="$wire.updateUserMbile({{ $user->id }}, value); edit = false">
                                                            <i class="fa fa-floppy-o fs-5"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td x-data="{ edit: false, value: '{{ $user->nationalCode }}' }">
                                                <div class="d-flex  align-items-center justify-content-center gap-1">
                                                    <input type="text" class="form-control form-control-sm"
                                                        x-show="edit" x-cloak x-model="value"
                                                        style="width: 110px" />
                                                    <span x-show="!edit" x-text="value || 'ثبت نشده'"
                                                        style="width: 110px"></span>

                                                    <div class="d-flex gap-2">
                                                        <a type="button" @click="edit = !edit" title="ویرایش">
                                                            <i class="fa fa-pencil-square-o fs-5"></i>
                                                        </a>

                                                        <a type="button" x-show="edit" x-cloak class="text-success"
                                                            title="ذخیره"
                                                            @click="$wire.updateNationalCode({{ $user->id }}, value); edit = false">
                                                            <i class="fa fa-floppy-o fs-5"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $user->document_number ?? 'ثبت نشده است' }}</td>
                                            <td>{{ $user->email ?? 'ثبت نشده است' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        {{-- seperator --}}
                        <div class="col-12 row my-5">
                            <div class="col-md-3">
                                <h5 class="text-info">
                                    <i class="fa fa-bookmark me-1" aria-hidden="true"></i>
                                    تاریخچه نوبت های بیمار
                                </h5>
                            </div>
                            <div class="col-md-9">
                                <hr>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="table-responsive">
                                <table class="table border  text-md-nowrap ">
                                    <thead>
                                        <tr class="text-center">
                                            <th>بخش</th>
                                            <th>مطب</th>
                                            <th>وضعیت نوبت</th>
                                            <th>نوع نوبت</th>
                                            <th>تاریخ نوبت</th>
                                            <th>تاریخ ویزیت</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center">
                                        @if ($fetchData['appointments']->isNotEmpty())
                                            @foreach ($fetchData['appointments'] as $key => $appointment)
                                                <tr class=" {{ $appointment->getColor() }} text-center">
                                                    <td>{{ $appointment->service->title ?? '-' }}</td>
                                                    <td>{{ $appointment->place->title ?? '-' }}</td>
                                                    <td>
                                                        <div>
                                                            <span>
                                                                {{ $appointment->status->getName() }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        {{ $appointment->kind->getName() }}
                                                        @if ($appointment->isOnline())
                                                            @if ($appointment->getUnseenMessageBadge() > 0)
                                                                <a class="bg-red text-white p-2 rounded-pill small"
                                                                    href="{{ route('admin.appointment_user.message.detail', ['onlineAppId' => $appointment->online->first()->id]) }}">
                                                                    {{ $appointment->getUnseenMessageBadge() }} پیام
                                                                    جدید
                                                                </a>
                                                            @else
                                                                <a class="bg-warning text-dark p-2 rounded-pill small"
                                                                    href="{{ route('admin.appointment_user.message.detail', ['onlineAppId' => $appointment->online->first()?->id]) }}">
                                                                    مشاهده چت</a>
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td>{{ verta($appointment->date_visit)->format('Y-m-d') }}</td>
                                                    <td>
                                                        @if ($appointment->visited_at)
                                                        @endif
                                                        {{ verta($appointment->visited_at)->format('Y-m-d') ?? 'ویزیت نشده' }}
                                                    </td>
                                                </tr>
                                            @endforeach

                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        {{-- seperator --}}
                        <div class="col-12 row my-5">
                            <div class="col-md-3">
                                <h5 class="text-info">
                                    <i class="fa fa-comment-o me-1" aria-hidden="true"></i>
                                    یادداشت
                                </h5>
                            </div>
                            <div class="col-md-9">
                                <hr>
                            </div>
                        </div>
                        <div class="col-lg-12 row">
                            @if ($fetchData['comments']->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table border text-nowrap text-md-nowrap table-striped">
                                        <thead>
                                            <tr>
                                                <th>متن پیام</th>
                                                <th>تاریخ ایجاد</th>
                                                <th>عملیات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($fetchData['comments'] as $comment)
                                                <tr>
                                                    <td>
                                                        <textarea disabled cols="70" rows="2"> {{ $comment->body }}</textarea>
                                                    </td>
                                                    <td>{{ verta($comment->created_at)->format('Y-m-d ساعت H:i') }}
                                                    </td>
                                                    <td><button class="btn btn-danger delete_confirm_alert"
                                                            data-id="{{ $comment->id }}"
                                                            data-label="یادداشت">حذف</button></td>
                                                </tr>
                                            @endforeach

                                            </li>
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                            <div class="col-12 mt-5">
                                <h5>افزودن یادداشت</h5>
                                <textarea class="form-control @error('form.comment') is-invalid @enderror" wire:model='form.comment'
                                    id="validationTextarea" placeholder="متن توضیحات را وارد کنید"></textarea>
                                @error('form.comment')
                                    <span class="text-danger">وارد کردن متن الزامی میباشد!</span>
                                @enderror
                            </div>
                            <div class="col-12">
                                <button wire:click='addComment' wire;target='addComment'
                                    wire:loading.class='btn-loading btn-gray' class="btn btn-success mt-3">اضافه
                                    کردن</button>
                            </div>
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

    <script>
        $(document).ready(function() {
            Livewire.on('showAlert', param => {
                showSwalSuccess(param.message)
            });
            Livewire.on('error', param => {
                showSwalError(param.message)
            });
        });
    </script>
@endpush]
