<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">افزودن سایت جدید</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">افزودن سایت</li>
                <li class="breadcrumb-item active" aria-current="page"><a href="javascript:void(0);">سایت ها</a></li>
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
                <form wire:submit='createOrUpdate' class="form-horizontal">
                    <div class="row mt-5 mb-3">
                        <label for="name" class="col-md-3 form-label">نام سایت:</label>
                        <div class="col-md-9">
                            <input class="form-control mb-1  @error('form.name') is-invalid @enderror"
                                   id="name" wire:model='form.name' placeholder="نام فارسی را وارد کنید"
                                   type="text">
                            @error('form.name')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mt-5 mb-3">
                        <label for="customer" class="col-md-3 form-label">نام مشتری:</label>
                        <div class="col-md-9">
                            <input class="form-control mb-1  @error('form.customer') is-invalid @enderror"
                                   id="customer" wire:model='form.customer' placeholder="نام مشتری را وارد کنید"
                                   type="text">
                            @error('form.customer')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mt-5 mb-3">
                        <label for="mobile" class="col-md-3 form-label">تلفن رابط مشتری:</label>
                        <div class="col-md-9">
                            <input class="form-control mb-1  @error('form.mobile') is-invalid @enderror"
                                   id="mobile" wire:model='form.mobile' placeholder="تلفن تماس مشتری"
                                   type="text">
                            @error('form.mobile')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mt-5 mb-3">
                        <label for="description" class="col-md-3 form-label">توضیحات</label>
                        <div class="col-md-9">
                        <textarea wire:model='form.description' class="form-control" rows="5" id="description   "
                                  placeholder="توضیحات اضافی"></textarea>
                        </div>
                    </div>
                    <div class="row mt-5 mb-3">
                        <label for="domain" class="col-md-3 form-label">آدرس دامنه:</label>
                        <div class="col-md-9">
                            <input class="form-control mb-1  @error('form.domain') is-invalid @enderror"
                                   id="domain" dir="ltr" wire:model='form.domain' placeholder="wwww.domain name.com"
                                   type="text">
                            @error('form.domain')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mt-5 mb-3">
                        <label for="id" class="col-md-3 form-label">site ID :</label>
                        <div class="col-md-9">
                            <input class="form-control mb-1  @error('form.id') is-invalid @enderror"
                                   id="id" dir="ltr" wire:model='form.id' placeholder="site id"
                                   type="text">
                            @error('form.id')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
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
                <button type="button" wire:click="createOrUpdate"
                   class="btn btn-secondary">بازگشت</button>
            </div>
        </div>
    </div>

</div>
