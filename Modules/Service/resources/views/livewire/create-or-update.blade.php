<div>
    <div>
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    @if ($isEdited)
                        ویرایش بخش <strong> {{ $service->title }} </strong>
                    @else
                        افزودن بخش
                    @endif
                </h1>
            </div>
            <div class="ms-auto pageheader-btn">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        @if ($isEdited)
                            ویرایش بخش
                        @else
                            افزودن بخش یا سرویس
                        @endif
                    </li>
                    <li class="breadcrumb-item active" aria-current="page"><a href="{{ route('admin.service.list') }}">بخش
                            ها</a></li>
                </ol>
            </div>
        </div>
        @if (isset($message))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <span class="alert-inner--text">{{ $message }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
        @endif
        <div class="row row-sm">
            <div class="card box-shadow-0">
                <div class="card-header border-bottom">
                    <h3 class="card-title">
                        @if ($isEdited)
                            ویرایش بخش
                        @else
                            افزودن بخش جدید
                        @endif
                    </h3>
                </div>
                <div class="card-body">
                    <form wire:submit='createOrUpdateSection' class="form-horizontal">
                        <div class="row mt-5 mb-3">
                            <label for="specialityName" class="col-md-3 form-label">نام بخش:</label>
                            <div class="col-md-9">
                                <input class="form-control mb-1  @error('form.title') is-invalid @enderror"
                                    id="specialityName" wire:model='form.title'
                                    placeholder="نام بخش یا سرویس مورد نظر را وارد کنید" type="text">
                                @error('form.title')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mt-5 mb-3">
                            <label for="js-select2" class="col-md-3 form-label">زیر بخش:</label>
                            <div class="col-md-9">
                                <div class="mb-3" wire:ignore>
                                    <select class="form-control select2-show-search form-select js-select2"
                                        id="js-select2" data-name="parent" data-placeholder="بدون والد">
                                        <option value="0">بدون والد</option>
                                        @if (isset($fetchdata['services']))
                                            @foreach ($fetchdata['services'] as $service)
                                                <option @if ($this->form['parent_id'] == $service->id) selected @endif
                                                    value="{{ $service->id }}">{{ $service->title }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <p class="text-muted mt-1 ms-1 mt-1 ms-1">
                                        <i class="fa fa-info-circle text-info text-info" aria-hidden="true"></i>
                                        بخشی که مایل هستید این بخش، زیر بخش آن بخش باشد را انتخاب کنید.
                                    </p>
                                    <p class="text-muted mt-1 ms-1 ms-1">
                                        <i class="fa fa-info-circle text-info text-info" aria-hidden="true"></i>
                                        در صورتی که گزینه ی بدون والد را انتخاب کنید ، این بخش به عنوان یک بخش اصلی
                                        اضافه میشود.
                                    </p>
                                </div>
                                @error('specialityTitle')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mt-4 mb-3" id="questionContainer" wire:ignore.self>
                            <label for="questionTitle" class="col-md-3 form-label  d-flex align-item-center">
                                <i class="fa fa-mobile fa-2x me-2 mb-1" aria-hidden="true"></i>
                                <span>عنوان سوال:</span>
                            </label>
                            <div class="col-md-9">
                                <textarea rows="3" class="form-control mb-1  @error('form.qestion') is-invalid @enderror"
                                    id="questionTitle" wire:model='form.qestion' type="number"></textarea>
                                @error('form.qestion')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <p class="text-muted mt-1 ms-1">
                                    <i class="fa fa-info-circle text-info" aria-hidden="true"></i>
                                    در صورتی که زیر بخش های این بخش باید در اپلیکیشن به صورت سوال نمایش داده شوند
                                    ،
                                    عنوان سوال را وارد کنید.
                                </p>
                            </div>
                        </div>
                        <div class="row mt-4 mb-3">
                            <label for="form_priority" class="col-md-3 form-label">ترتیب نمایش:</label>
                            <div class="col-md-9">
                                <input class="form-control mb-1  @error('form.priority') is-invalid @enderror"
                                    id="form_priority" wire:model='form.priority' type="number">
                                @error('form.priority')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <p class="text-muted mt-1 ms-1">
                                    <i class="fa fa-info-circle text-info" aria-hidden="true"></i>
                                    بخش ها به ترتب شماره گذاری نمایش داده میشوند.
                                </p>
                            </div>
                        </div>
                        <div class="row mt-5 mb-3">
                            <label for="place-select2" class="col-md-3 form-label">اختصاص به مطب:</label>
                            <div class="col-md-9" wire:ignore>
                                <div class="mb-3">
                                    <select id="place-select2" data-name="place" multiple
                                        class="form-control select2-show-search form-select js-select2"
                                        data-placeholder="مطب اختصاصی">
                                        <option value="0">انتخاب کنید..</option>
                                        @if (isset($fetchdata['places']))
                                            @foreach ($fetchdata['places'] as $place)
                                                <option @if (isset($this->form['place']) &&
                                                                ! is_null($this->form['place']) &&
                                                 in_array($place->id, $this->form['place'])) selected @endif
                                                    value="{{ $place->id }}">{{ $place->title }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <p class="text-muted mt-1 ms-1">
                                        <i class="fa fa-info-circle text-info" aria-hidden="true"></i>
                                        با انتخاب مطب، بخش در سایر مطب ها دیده نمی شود.
                                    </p>
                                </div>
                                @error('specialityTitle')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mt-4 mb-3">
                            <label for="form_api_code" class="col-md-3 form-label">کد نرم افزاری سلاک طب:</label>
                            <div class="col-md-9">
                                <input class="form-control mb-1  @error('form.api_code') is-invalid @enderror"
                                       id="form_api_code" wire:model='form.api_code' type="number">
                                @error('form.api_code')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <p class="text-muted mt-1 ms-1">
                                    <i class="fa fa-info-circle text-info" aria-hidden="true"></i>
                                    بخش ها به ترتب شماره گذاری نمایش داده میشوند.
                                </p>
                            </div>
                        </div>
                        <div class="row mt-4 mb-3">
                            <label for="check_showType" class="col-md-3 form-label">نمایش در صفحه اصلی:</label>
                            <div class="col-md-9">
                                <div class="custom-checkbox custom-control">
                                    <input type="checkbox" wire:model='form.show_type' data-checkboxes="mygroup"
                                        class="custom-control-input" checked id="check_showType">
                                    <label for="check_showType" class="custom-control-label">با فعال سازی ، این بخش در
                                        صفحه اصلی وبسایت نمایش داده میشود
                                    </label>
                                </div>
                                @error('form.priority')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mt-4 mb-3">
                            <label for="customShow" class="col-md-3 form-label">عدم نمایش بخش به کاربر:</label>
                            <div class="col-md-9">
                                <div class="custom-checkbox custom-control">
                                    <input type="checkbox" wire:model='form.notShowToUser' data-checkboxes="mygroup"
                                        class="custom-control-input" checked id="customShow">
                                    <label for="customShow" class="custom-control-label">بخش برای استفاده های مدیریتی میباشد و به کاربر نمایش داده نمیشود
                                    </label>
                                </div>
                                @error('form.notShowToUser')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-5 mt-3">
                            <x-admin.core.form.image-upload
                                label="ایکون بخش در سایت"
                                uploadedPhotoUrl="{{ $uploadedPhotoUrl }}"
                                uploadedFileName="{{ $uploadedFileName }}"
                                uploadedFileType="{{ $uploadedFileType }}"
                                deleteAction="deleteFile"
                                model="photo"
                                id="fileUpload"
                            />
                        </div>
                        @if (!empty($fetchdata['doctors']))
                            <div class="row mt-5">
                                <h4>پزشکان مربوط به این بخش</h4>
                                <input type="text" id="doctor-search" placeholder="جستجوی پزشک..."
                                    class="form-control mb-3">
                                <hr style="opacity: 0.9">
                                <div class="row">
                                    @foreach ($fetchdata['doctors'] as $key => $doctor)
                                        <div class="col-md-6 doctor-item">
                                            <div class="form-group mt-2">
                                                <div class="checkbox">
                                                    <div class="custom-checkbox custom-control">
                                                        <input type="checkbox"
                                                            wire:model='form.doctors.{{ $doctor->id }}'
                                                            data-checkboxes="mygroup" class="custom-control-input"
                                                            id="checkbox-{{ $key }}">
                                                        <label for="checkbox-{{ $key }}"
                                                            class="custom-control-label">{{ $doctor->full_name }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <div class="row mt-5">
                            <h4>وضعیت بخش</h4>
                            <hr style="opacity: 0.9">
                            <div class="form-group">
                                <div class="checkbox">
                                    <div class="custom-checkbox custom-control">
                                        <input type="checkbox" wire:model='form.active' data-checkboxes="mygroup"
                                            class="custom-control-input"
                                            @if ($form['active'] == true) checked @endif id="checkbox">
                                        <label for="checkbox" class="custom-control-label">فعال</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group mt-3  d-flex justify-content-end">
                                <div>
                                    <button type="submit" class="btn btn-success"
                                        wire:loading.class="bg-gray btn-loading disabled">ذخیره
                                    </button>
                                    <a type="button" href="{{ route('admin.speciality.index') }}"
                                        class="btn btn-secondary">بازگشت</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
    <livewire:admin::file-manager-modal />
</div>

@push('styles')
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endpush
@push('scripts')
    <script src="{{ admin_asset('plugins/select2/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            setTimeout(() => {
                $('.js-select2').select2();
            }, 500);
            $('.js-select2').on('select2:select select2:unselect', function(e) {
                var name = $(this).data('name');
                var value = $(this).val();
                @this.set('form.' + name, value);
                if (name == 'parent') {
                    if (value == 0) {
                        $('#questionContainer').fadeIn();
                    } else {
                        $('#questionContainer').fadeOut();
                    }
                }
            });
            if ($('#js-select2').val() != 0) {
                $('#questionContainer').css("display", "none");
            };
            Livewire.on('select_file', (param) => {
                @this.set('form.img', param.url);
                //close modal
                $('#file-selector-modal').modal('hide');
            });
            $('body').on('keyup', '#doctor-search', function() {
                var value = $(this).val().toLowerCase();
                $(".doctor-item").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
@endpush
