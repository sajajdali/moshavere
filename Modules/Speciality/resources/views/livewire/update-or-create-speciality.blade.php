<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">افزودن تخصص</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">افزودن تخصص</li>
                <li class="breadcrumb-item active" aria-current="page"><a href="javascript:void(0);">تخصص ها</a></li>
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
                <h3 class="card-title">افزودن تخصص جدید</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">برای اضافه کردن تخصص، نام تخصص را وارد کرده و روی گزینه ی ذخیره کلیک کنید..</p>
                <form wire:submit='createSpeciality' class="form-horizontal">
                    <div class="row mt-5 mb-3">
                        <label for="specialityName" class="col-md-3 form-label">نام تخصص:</label>
                        <div class="col-md-9">
                            <input class="form-control mb-1  @error('specialityTitle') is-invalid @enderror"
                                id="specialityName" wire:model='specialityTitle' placeholder="نام تخصص را وارد کنید"
                                type="text">
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
                            <p class="text-muted">تخصص ها به ترتب شماره گذاری نمایش داده میشوند.</p>
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
                    @if (!empty($doctors))
                        <div class="row mt-5">
                            <h4>اختصاص این تخصص به پزشکان</h4>
                            <hr style="opacity: 0.9">
                            <div class="row">
                                <h5>لیست پزشکان</h5>
                                <p class="text-muted my-1">لطفا پزشکانی که مایل هستید این تخصص را داشته باشند انتخاب
                                    کنید</p>
                                @foreach ($doctors as $key => $doctorList)
                                    <div class="col-md-6">
                                        <div class="form-group mt-2">
                                            <div class="checkbox">
                                                <div class="custom-checkbox custom-control">
                                                    <input type="checkbox" wire:model='doctor.{{ $doctorList->id }}'
                                                        @if (array_key_exists($doctorList->id, $doctor) && $doctor[$doctorList->id] == 'true') checked @endif
                                                        data-checkboxes="mygroup" class="custom-control-input"
                                                        id="checkbox-{{ $key }}">
                                                    <label for="checkbox-{{ $key }}"
                                                        class="custom-control-label">{{ $doctorList->full_name }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <div class="row">
                        <div class="form-group mt-3  d-flex justify-content-end">
                            <div>
                                <button type="submit" class="btn btn-success">
                                    <span wire:loading.remove>ذخیره</span>
                                    <div wire:loading class="spinner-border spinner-border-sm text-light"
                                        role="status">
                                    </div>
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
