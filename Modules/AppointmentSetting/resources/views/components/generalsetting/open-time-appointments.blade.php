<div class="card-header border-bottom d-flex justify-content-between">
    <h3 class="d-flex align-item-center">
        <i class="fa fa-clock-o me-2 d-none d-sm-inline" aria-hidden="true"></i>
        <span>
            زمان <span class="text-primary"> باز شدن </span>نوبت ها
        </span>
    </h3>
    <div class="main-toggle-group d-sm-flex align-item-center ms-0">
        <div class="toggle toggle-lg toggle-primary my-1  customCheckbox @if (isset($form['openTime']['time'])) on  @else off @endif"
            data-id="openTime.status" wire:ignore.self data-bs-toggle="collapse" href="#openTimeCollaps" role="button"
            aria-expanded="false" aria-controls="openTimeCollaps">
            <span></span>
        </div>
    </div>
</div>
<div class="collapse @if (isset($form['openTime']['time'])) show @endif "  id="openTimeCollaps" wire:ignore.self>
    <div class="card-body">
        @error('form.openTime.time')
            <div class="alert alert-danger" role="alert">
                <p class="text-danger"> در صورت فعال کردن باید ساعت را مشخص کنید!!
                </p>
            </div>
        @enderror
        {{-- section --}}
        <div class="row">
            <div class="col-12 mb-2">
                <div class="row">
                    <label for="input-time" class="form-label col-md-3">در ساعت</label>
                    <input wire:model='form.openTime.time'
                        type="time" wire:ignore.self class="form-control col-md-9" id="input-time">
                </div>
            </div>
            <div class="d-flex flex-column mt-2">
                <p class="text-muted"><strong class="me-1"> نکته!! </strong> نوبت های روز جدید، در چه ساعتی قابل دریافت باشند</p>
                <br/>
                <small class="text-muted">
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                    برای مثال در صورتی که حداکثر زمان دریافت نوبت را 7 روز قرار دادید، نوبت های روز 8 ام در چه ساعتی برای بیماران باز بشود
                </small>
            </div>
        </div>
    </div>
</div>
