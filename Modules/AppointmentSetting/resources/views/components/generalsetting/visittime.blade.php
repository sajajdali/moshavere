<div class="card-header pb-0">
    <h3 class="d-flex align-items-center">
        <i class="fa fa-clock-o me-2 d-none d-sm-inline" aria-hidden="true"></i>
        <span>
            زمان مورد نیاز برای <span class="text-primary">ویزیت</span> هر بیمار
        </span>
    </h3>
</div>
<div class="card-body pt-0">
    @if (isset($form['visitType']['online']) && $form['visitType']['online'] == true)
        <div class="row my-4">
            <div class="col-sm-3 text-secondary">
                <h4>
                    <i class="fa fa-user fa-xl" aria-hidden="true"></i>
                    ویزیت حضوری
                </h4>
            </div>
            <div class="col-sm-9">
                <hr class="bg-secondary">
            </div>
        </div>
    @endif
    <hr style="opacity: 0.5">
    <div class="row">
        @error('form.visitTime')
            <div class="alert alert-danger" role="alert">
                <p class="text-danger"><strong>خطا!!</strong> {{ $message }}.</p>
            </div>
        @enderror
        <div class="col-md-4 pt-2">
            <label class="text-primary" for="basic-url">مدت زمان مورد نیاز برای ویزیت هر بیمار</label>
        </div>
        <div class="col-md-8">
            <div class="input-group mb-3">
                <input type="number" class="form-control @error('form.visitTime') is-invalid @enderror" id="basic-url"
                    aria-describedby="basic-addon3" wire:model='form.visitTime'>
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon3">مدت زمان به دقیقه</span>
                </div>
            </div>
        </div>
        <div class="d-flex  mt-2">
            <p class="text-muted">
                <strong class="me-1"> نکته!! </strong> مدت زمان هر نوبت، برای محاسبه تعداد نوبت های هر روز
                استفاده میشود، برای اینکه در یک روز 8 ساعته
                8 نوبت داشته باشید، مدت زمان ویزیت را 60 دقیقه تنظیم کنید.
            </p>
        </div>
    </div>
    @if (isset($form['visitType']['online']) && $form['visitType']['online'] == true)
        <div class="row my-5">
            <div class="col-sm-3 text-secondary">
                <h4>
                    <i class="fa fa-television fa-xl me-1" aria-hidden="true"></i>
                    ویزیت آنلاین
                </h4>
            </div>
            <div class="col-sm-9">
                <hr class="bg-secondary">
            </div>
        </div>
        <div class="row">
            @error('form.onlinevisit.time')
                <div class="alert alert-danger" role="alert">
                    <p class="text-danger"><strong>خطا!!</strong> {{ $message }}.</p>
                </div>
            @enderror
            <div class="col-md-4 pt-2">
                <label class="text-primary" for="basic-url">مدت زمان فعال بودن نوبت آنلاین</label>
            </div>
            <div class="col-md-8">
                <div class="input-group mb-3">
                    <input type="number" class="form-control @error('form.onlinevisit.time') is-invalid @enderror"
                        id="basic-url" aria-describedby="basic-addon3" wire:model='form.onlinevisit.time'>
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon3">مدت زمان به ساعت</span>
                    </div>
                </div>
            </div>
            <div class="d-flex  mt-2">
                <p class="text-muted">
                    <strong class="me-1"> نکته!! </strong> این زمان ، تعیین کننده ی مدت زمان فعال هر نوبت آنلاین است و
                    بعد از گذشت این زمان ، نوبت به حالت تمام شده تغییر وضعیت میدهد
                </p>
            </div>
        </div>
    @endif
</div>
