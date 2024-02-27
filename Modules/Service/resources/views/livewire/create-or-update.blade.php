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
                    <form wire:submit='createSection' class="form-horizontal">
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
                            <label for="specialityName" class="col-md-3 form-label">زیر بخش:</label>
                            <div class="col-md-9">
                                <div class="mb-3">
                                    <select class="form-control select2-show-search form-select" id="js-select2"
                                        data-placeholder="بدون والد">
                                        <option label="بدون والد"></option>
                                        <option value="1">Chuck Testa</option>
                                        <option value="2">Sage Cattabriga-Alosa</option>
                                        <option value="3">Nikola Tesla</option>
                                        <option value="4">Cattabriga-Alosa</option>
                                        <option value="5">Nikola Alosa</option>
                                        <option value="6">Chuck Testa</option>
                                        <option value="7">Sage Cattabriga-Alosa</option>
                                        <option value="8">Nikola Tesla</option>
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
                            <label for="priority" class="col-md-3 form-label">ترتیب نمایش:</label>
                            <div class="col-md-9">
                                <input class="form-control mb-1  @error('priority') is-invalid @enderror" id="priority"
                                    wire:model='priority' type="number">
                                @error('priority')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <p class="text-muted">
                                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                                    بخش ها به ترتب شماره گذاری نمایش داده میشوند.
                                </p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-row">
                                <label for="password">تصویر بخش</label>
                                <div class="input-group">
                                    <span class="input-group-btn">
                                        <button data-for="serviceImg" data-variable="serviceImg"
                                            class="btn btn-primary select_file" data-bs-target="#file-selector-modal"
                                            data-bs-toggle="modal" type="button">
                                            <i class="fa fa-picture-o"></i>
                                            انتخاب تصویر
                                        </button>
                                    </span>
                                    <input id="thumbnail" class="form-control" type="text" name="filepath"
                                        wire:model="serviceImg">
                                </div>
                                <img id="holder" style="margin-top:15px;max-height:100px;"
                                    src="{{ $serviceImg }}" />
                            </div>
                        </div>
                        <div class="form-group mt-5">
                            <div class="checkbox">
                                <div class="custom-checkbox custom-control">
                                    <input type="checkbox" wire:model='status' data-checkboxes="mygroup"
                                        class="custom-control-input" checked id="checkbox">
                                    <label for="checkbox" class="custom-control-label">فعال</label>
                                </div>
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
                                                            wire:model='form.doctor.{{ $doctor->id }}'
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
                        @else
                        @endif
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
@push('scripts')
    <script src="{{ admin_asset('plugins/select2/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            setTimeout(() => {
                $('#js-select2').select2({
                    'width': '100%',
                });
                // $('.select2-container').css('width', '100% !important');
            }, 1000);
            Livewire.on('select_file', (param) => {
                @this.set('serviceImg', param.url);
                //close modal
                $('#file-selector-modal').modal('hide');
            });

        });
    </script>
@endpush
