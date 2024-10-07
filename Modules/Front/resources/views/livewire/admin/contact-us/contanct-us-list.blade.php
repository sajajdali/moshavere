<div>
    <div>
        <div class="page-header">
            <div>
                <h1 class="page-title">فرم های تماس با ما</h1>
            </div>
        </div>
        @if ($alertMessage)
            <div class="col-md-12 alert alert-success" id="customAlertMessage" role="alert">
                <i class="fa fa-check-square-o me-1 fa-xl" aria-hidden="true"></i>
                {{ $alertMessage }}
            </div>
        @endif
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
                            @if (isset($form['search']))
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
                        @if (isset($form['search'])) show @endif"
                            id="advanceSearch" wire:ignore>
                            <form class="form-horizontal example" autocomplete="off">
                                <div class="row mb-4">
                                    <label for="search-id" class="col-md-2 form-label">ایدی</label>
                                    <div class="col-md-10">
                                        <input class="form-control" id="search-id" wire:model="form.search.id"
                                            feedBackholder="ایدی مطب مورد نظر" type="text">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <label for="search-user_name" class="col-md-2 form-label">نام کاربر</label>
                                    <div class="col-md-10">
                                        <input class="form-control" id="search-user_name"
                                            wire:model="form.search.name" feedBackholder="کد" type="text">
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
                                        <th scope="col">درخواست</th>
                                        <th scope="col">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($contactForms->isNotEmpty())
                                        @foreach ($contactForms as $contact)
                                            <tr class="text-center">
                                                <td>{{ $contact->id }}</td>
                                                <td>{{ $contact->getUserName() }}
                                                <td>
                                                    <a data-bs-toggle="modal" data-bs-target="#contactModal"
                                                        class="text-primary" href="#"
                                                        wire:click='openModal("{{ $contact->id }}")'>

                                                        {{ substr($contact->body, 0, 50) }}...
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="btn-group mt-2 mb-2">
                                                        <button type="button"
                                                            class="btn {{ $contact->getButtonColor() }} dropdown-toggle"
                                                            data-bs-toggle="dropdown">
                                                            {{ $contact->getStatusName() }}
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu" role="menu">
                                                            <li>
                                                                <a class="delete_confirm_alert" data-label="کامنت"
                                                                    data-id="{{ $contact->id }}" href="#">
                                                                    <i class="fa fa-trash text-danger"
                                                                        aria-hidden="true"></i>
                                                                    حذف</a>
                                                            </li>
                                                            @if (isset($contact->detail[\Modules\Front\app\Models\Contactus::DETAIL_REPORT_HANDEL]) &&
                                                                    $contact->detail[\Modules\Front\app\Models\Contactus::DETAIL_REPORT_HANDEL] == "true")
                                                                <li>
                                                                    <a
                                                                        wire:click='markfordone("{{ $contact->id }}","false")'
                                                                        href="#">
                                                                        <i class="fa fa-repeat" aria-hidden="true"></i>
                                                                        تغییر وضعیت به بررسی نشده</a>
                                                                </li>
                                                            @else
                                                                <li>
                                                                    <a
                                                                        wire:click='markfordone("{{ $contact->id }}","true")'
                                                                        href="#">
                                                                        <i class="fa fa-repeat" aria-hidden="true"></i>
                                                                        تغییر وضعیت به بررسی شده</a>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </div>
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
                            {{ $contactForms->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" wire:ignore.self
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactModalLabel">متن نوشته شده توسط کاربر</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" wire:loading.class='opacity-25'>
                    <div class="card">
                        <div class="card-body">
                            @isset($fetchData['modal']['commentCody'])
                                <h4 class="mb-4">متن</h4>
                                <p>
                                    {{ $fetchData['modal']['commentCody'] }}
                                </p>
                            @endisset

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">بستن</button>
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
                var modalElement = document.getElementById('contactModal');
                var contactFormsModalAwns = bootstrap.Modal.getInstance(modalElement) || new bootstrap
                    .Modal(modalElement, {
                        keyboard: false
                    });
                contactFormsModalAwns.hide();
            });
        });
    </script>
@endpush
