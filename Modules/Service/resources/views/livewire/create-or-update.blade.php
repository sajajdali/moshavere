<div>
    <div>
        <div class="page-header">
            <div>
                <h1 class="page-title">افزودن بخش</h1>
            </div>
            <div class="ms-auto pageheader-btn">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">افزودن بخش یا سرویس</li>
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
                    <h3 class="card-title">افزودن بخش جدید</h3>
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
                                <div class="mb-3">
                                    <select class="form-control select2-show-search form-select" id="js-select2"
                                        data-placeholder="بدون والد">
                                        <option value="0">بدون والد</option>
                                        @if (isset($fetchdata['services']))
                                            @foreach ($fetchdata['services'] as $service)
                                                <option @if ($this->form['parent_id'] == $service->id) selected @endif
                                                    value="{{ $service->id }}">{{ $service->title }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <p class="text-muted">
                                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                                        بخشی که مایل هستید این بخش، زیر بخش آن بخش باشد را انتخاب کنید.
                                    </p>
                                    <p class="text-muted">
                                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                                        در صورتی که گزینه ی بدون والد را انتخاب کنید ، این بخش به عنوان یک بخش اصلی
                                        اضافه میشود.
                                    </p>
                                </div>
                                @error('specialityTitle')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
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
                                <p class="text-muted">
                                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                                    بخش ها به ترتب شماره گذاری نمایش داده میشوند.
                                </p>
                            </div>
                        </div>
                        <div class="row mt-4 mb-3">
                            <label for="form_priority" class="col-md-3 form-label">نمایش در صفحه اصلی:</label>
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
                        <div class="row mb-5 mt-3">
                            <div class="form-row">
                                <label for="password">تصویر بخش</label>
                                <div class="input-group">
                                    <span class="input-group-btn">
                                        <button data-for="form.img" data-variable="form.img"
                                            class="btn btn-primary select_file" data-bs-target="#file-selector-modal"
                                            data-bs-toggle="modal" type="button">
                                            <i class="fa fa-picture-o"></i>
                                            انتخاب تصویر
                                        </button>
                                    </span>
                                    <input id="thumbnail"
                                        class="form-control    @error('form.img') is-invalid   @enderror" type="text"
                                        name="filepath" wire:model="form.img">

                                </div>
                                @error('form.img')
                                    <strong class="text-danger mt-1">{{ $message }}</strong>
                                @enderror
                                @if (isset($form['img']))
                                    <img id="holder" style="margin-top:15px;max-height:100px;"
                                        src="{{ $form['img'] }}" />
                                @endif
                            </div>
                        </div>
                        @if (!empty($fetchdata['doctors']))
                            <div class="row mt-5">
                                <h4>پزشکان مربوط به این بخش</h4>
                                <hr style="opacity: 0.9">
                                <div class="row">
                                    @foreach ($fetchdata['doctors'] as $key => $doctor)
                                        <div class="col-md-6">
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
                $('#js-select2').select2();
            }, 1000);
            $('#js-select2').on('select2:select', function(e) {
                @this.set('form.parent_id', $(this).val());
            });
            Livewire.on('select_file', (param) => {
                @this.set('form.img', param.url);
                //close modal
                $('#file-selector-modal').modal('hide');
            });

        });
    </script>
@endpush
