<div>
    <div>
        <div class="page-header">
            <div>
                <h1 class="page-title">کامنت ها</h1>
            </div>
        </div>
        <div class="col-md-12 alert alert-success" id="customAlertMessage" role="alert" style="display: none" wire:ignore>
            <p id="customAlertMessagebody"></p>
        </div>
        @include('admin::layouts.components.alert')
        <div class="row row-sm">
            <div class="col-lg-12">
                <div class="card custom-card">
                    <div class="card-header d-flex justify-content-between border-bottom">
                        <h3 class="card-title"></h3>
                        <div class="card-options">
                            <button class="btn btn-primary" type="button" data-bs-toggle="collapse"
                                data-bs-target="#advanceSearch" aria-expanded="false" aria-controls="advanceSearch">
                                جست و جوی پیشرفته
                            </button>
                            @if (
                                !empty($search['id']) ||
                                    !empty($search['userName']) ||
                                    !empty($search['doctorName']) ||
                                    !empty($search['serviceName']))
                                <button class="btn btn-secondary ms-2" type="button" wire:click="resetProperties"
                                    data-bs-toggle="collapse" data-bs-target="#advanceSearch" aria-expanded="false"
                                    aria-controls="advanceSearch"
                                    wire:loading.class="bg-gray btn-loading disabled">نمایش همه
                                </button>
                            @endif
                        </div>

                    </div>
                    <div class="card-body">
                        <div class="mb-5 collapse
                        @if (
                            !empty($search['id']) ||
                                !empty($search['userName']) ||
                                !empty($search['doctorName']) ||
                                !empty($search['serviceName'])) show @endif"
                            id="advanceSearch" wire:ignore>
                            <form class="form-horizontal example" autocomplete="off">
                                <div class="row mb-4">
                                    <label for="search-id" class="col-md-2 form-label">ایدی</label>
                                    <div class="col-md-10">
                                        <input class="form-control" id="search-id" wire:model="search.id"
                                            feedBackholder="ایدی مطب مورد نظر" type="text">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <label for="search-user_name" class="col-md-2 form-label">نام کاربر</label>
                                    <div class="col-md-10">
                                        <input class="form-control" id="search-user_name" wire:model="search.userName"
                                            feedBackholder="کد" type="text">
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <label for="search-name" class="col-md-2 form-label">نام پزشک</label>
                                    <div class="col-md-10">
                                        <input class="form-control" id="search-name" wire:model="search.doctorName"
                                            feedBackholder="کد" type="text">
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
                                    <tr class="table-primary">
                                        <th scope="col">#</th>
                                        <th scope="col">نام کاربر</th>
                                        <th scope="col">نام پزشک</th>
                                        <th scope="col">تاریخ</th>
                                        <th scope="col">وضعیت</th>
                                        <th scope="col">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($comments->isNotEmpty())
                                        @foreach ($comments as $comment)
                                            <tr class="text-center">
                                                <td>{{ $comment->id }}</td>
                                                <td>{{ $comment->user->fullname }}
                                                </td>
                                                <td>{{ $comment->doctor->fullname }}
                                                </td>
                                                <td>{{ verta($comment->created_at)->format('Y/m/d') }}
                                                </td>
                                                <td>
                                                    {{ $comment->status->getName() }}
                                                </td>
                                                <td>
                                                    @canany(['update', 'delete'], $comment)
                                                        <div class="btn-group mt-2 mb-2">
                                                            <button type="button"
                                                                class="btn {{ $comment->status->getButtonColor() }} dropdown-toggle"
                                                                data-bs-toggle="dropdown">
                                                                {{ $comment->status->getName() }}
                                                                <span class="caret"></span>
                                                            </button>
                                                            <ul class="dropdown-menu" role="menu">
                                                                <li>
                                                                    <a wire:click='editAppointment("{{ $comment->id }}")'
                                                                        href="#">
                                                                        <i class="fa fa-pencil-square-o"
                                                                            aria-hidden="true"></i>
                                                                        ویرایش</a>
                                                                </li>
                                                                <li>
                                                                    <a wire:click='editAppointment("{{ $comment->id }}")'
                                                                        href="#">
                                                                        <i class="fa fa-check text-success"
                                                                            aria-hidden="true"></i>
                                                                        تایید کردن</a>
                                                                </li>
                                                                <li>
                                                                    <a data-bs-toggle="modal" data-bs-target="#commentModal"
                                                                        wire:click='lunchModal("{{ $comment->id }}")'
                                                                        href="#">
                                                                        <i class="fa fa-commenting" aria-hidden="true"></i>
                                                                        پاسخ دادن</a>
                                                                </li>
                                                                <li>
                                                                    <a wire:click='editAppointment("{{ $comment->id }}")'
                                                                        href="#">
                                                                        <i class="fa fa-trash text-danger"
                                                                            aria-hidden="true"></i>
                                                                        حذف</a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    @else
                                                        <div class="btn-group mt-2 mb-2">
                                                            <button type="button" class="btn btn-default dropdown-toggle"
                                                                data-bs-toggle="dropdown">
                                                                عملیات <span class="caret"></span>
                                                            </button>
                                                        </div>
                                                    @endcan
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
                            {{ $comments->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" wire:ignore.self
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="commentModalLabel">پاسخ به کامنت</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" wire:loading.class='opacity-25'>
                    <div class="card">
                        <div class="card-body">
                            @isset($fetchData['operation']['comment'])
                                <h4 class="mb-4">متن کامنت</h4>
                                <p>
                                    {{ $fetchData['operation']['comment']->body }}
                                </p>
                                <div class="d-flex justify-content-between">
                                    <span>
                                        @for ($i = 0; $i < $fetchData['operation']['comment']->star; $i++)
                                            <i class="fa fa-star text-warning" aria-hidden="true"></i>
                                        @endfor
                                    </span>
                                </div>
                            @endisset
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="mb-5">پاسخ به کامنت</h4>
                            <div class="form-group">
                                <label for="textarea" class="form-label">متن پاسخ را تایپ کنید</label>
                                <textarea class="form-control  @error('form.reply') is-invalid @enderror" wire:model='form.reply' maxlength="500"
                                    rows="3"></textarea>
                                @error('form.reply')
                                    <span>لطفا متن کامنت را وارد کنید!</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">بستن</button>
                    <button type="button" class="btn btn-success" wire:loading.class='btn-loading'
                        wire:loading.attr='disabled' wire:click='storeAnswer'>ذخیره</button>
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
            Livewire.on('closeModal', function() {
                var modalElement = document.getElementById('commentModal');
                var commentsModalAwns = bootstrap.Modal.getInstance(modalElement) || new bootstrap
                    .Modal(modalElement, {
                        keyboard: false
                    });
                commentsModalAwns.hide();
            });
            Livewire.on('message', function($message) {
                var alert = $('#customAlertMessage');
                console.log(alert.css('display'),$message);
                if (alert.css('display') === 'none') {
                    $('#customAlertMessagebody').text($message.message);
                    alert.fadeIn();
                }
            });
        });
    </script>
@endpush
