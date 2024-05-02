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
                            <div class="table-responsive">
                                <table class="table border text-nowrap text-md-nowrap table-striped">
                                    <thead>
                                        <tr>
                                            <th>نام</th>
                                            <th>موبایل</th>
                                            <th>شماره پرونده</th>
                                            <th>ایمیل</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ $user->fullName ?? 'ثبت نشده است' }}</td>
                                            <td>{{ $user->mobile ?? 'ثبت نشده است' }}</td>
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
                                    نوبت های گذشته
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
                                        <tr>
                                            <th>بخش</th>
                                            <th>مطب</th>
                                            <th>وضعیت نوبت</th>
                                            <th>نوع نوبت</th>
                                            <th>تاریخ نوبت</th>
                                            <th>تاریخ ویزیت</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($fetchData['appointments']->isNotEmpty())
                                            @foreach ($fetchData['appointments'] as $key => $appointment)
                                                <tr class=" {{ $appointment->getColor() }}">
                                                    <td>{{ $appointment->service->title ?? '-' }}</td>
                                                    <td>{{ $appointment->place->title ?? '-' }}</td>
                                                    <td>{{ $appointment->status->getName() }}</td>
                                                    <td>{{ $appointment->kind->getName() }}</td>
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
                                                    <td><textarea disabled cols="70" rows="2" > {{$comment->body}}</textarea></td>
                                                    <td>{{ verta($comment->created_at)->format('Y-m-d ساعت H:i') }}</td>
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
@endpush]
