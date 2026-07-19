<div class="card-header pb-0">
    <h3 class="d-flex align-items-center">
        <i class="fa fa-leaf me-2 fa-xl d-none d-sm-inline" aria-hidden="true"></i>
        <span>
            نوع ویزیت
        </span>
    </h3>
</div>
<div class="card-body pt-0 px-5">
    {{-- section --}}
    <hr style="opacity: 0.5">
    <div class="row">
        @if ($errors->has('form.visitType.inPerson') || $errors->has('form.visitType.online') || $errors->has('form.visitType.voip'))
            <div class="alert alert-danger" role="alert">
                <p class="text-danger"><strong>خطا!!</strong> لطفا نوع ویزیت را تعیین کنید</p>
            </div>
        @endif
        <div class="col-md-4 mt-3">
            <div class="main-toggle-group d-flex align-items-center ms-0">
                <div class="toggle toggle-lg toggle-primary my-1 customCheckbox @if (isset($form['visitType']['inPerson']) && $form['visitType']['inPerson']) on @else off @endif"
                    wire:ignore.self data-id="visitType.inPerson">
                    <span></span>
                </div>
                <div class="ms-2">
                    <p class="text-muted m-0">حضوری</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mt-3">
            <div class="main-toggle-group d-flex align-items-center ms-0">
                <div class="toggle toggle-lg toggle-primary my-1 customCheckbox @if (isset($form['visitType']['online']) && $form['visitType']['online']) on @else off @endif"
                    wire:ignore.self data-id="visitType.online">
                    <span></span>
                </div>
                <div class="ms-2">
                    <p class="text-muted m-0">آنلاین</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mt-3">
            <div class="main-toggle-group d-flex align-items-center ms-0">
                <div class="toggle toggle-lg toggle-primary my-1 customCheckbox @if (isset($form['visitType']['voip']) && $form['visitType']['voip']) on @else off @endif"
                    wire:ignore.self data-id="visitType.voip">
                    <span></span>
                </div>
                <div class="ms-2">
                    <p class="text-muted m-0">تلفنی</p>
                </div>
            </div>
        </div>
    </div>
    <div @if (isset($form['visitType']['online']) && $form['visitType']['online']) style="display: block" @else style="display: none" @endif
        class="row mt-4">
        <hr>
        <div class="d-flex align-items-center">
            <div class="main-toggle-group d-sm-flex align-items-center ms-0">
                <div class="toggle toggle-lg toggle-primary my-1  customCheckbox
                    @if (isset($form['accessibility']['online']['can_send_voice']) &&
                            $form['accessibility']['online']['can_send_voice'] == true) on
                        @else
                        off @endif"
                    data-id="accessibility.online.can_send_voice" id="accessibilityOnline_voice"
                    wire:ignore.self>
                    <span></span>
                </div>
            </div>
            <span class="ms-2">امکان ارسال ویس برای کاربران در نوبت دهی آنلاین فعال باشد؟</span>
        </div>
    </div>
    <div class="row mt-4">
        <hr>
        <div class="d-flex align-items-center">
            <div class="main-toggle-group d-sm-flex align-items-center ms-0">
                <div class="toggle toggle-lg toggle-primary my-1  customCheckbox
                    @if (isset($form['accessibility']['dont_show_times']['status']) &&
                            $form['accessibility']['dont_show_times']['status'] == true) on
                        @else
                        off @endif"
                    data-id="accessibility.dont_show_times.status" id="accessibilityOnline_voice"
                    wire:ignore.self>
                    <span></span>
                </div>
            </div>
            <span class="ms-2">غیر فعال بودن مشاهده ساعت های دکتر</span>
        </div>
        @if (isset($form['accessibility']['dont_show_times']['status']) &&
                $form['accessibility']['dont_show_times']['status'] == true)
            <br>
            <div class="d-flex align-items-center">
                <textarea rows="3" class="form-control mt-5 ms-1 " wire:model="form.accessibility.dont_show_times.message"
                    placeholder=" متن پیغام نمایشی در صورتی غیر فعال بودن "></textarea>
            </div>
        @endif
    </div>
    <div class="row mt-4 @if (isset($form['visitType']['online']) && $form['visitType']['online']) d-block @else d-none @endif">
        <hr>
        <div class="d-flex align-items-center">
            <div class="main-toggle-group d-sm-flex align-items-center ms-0">
                <div class="toggle toggle-lg toggle-primary my-1  customCheckbox
                    @if (isset($form['accessibility']['disable_online']['status']) &&
                            $form['accessibility']['disable_online']['status'] == true) on
                        @else
                        off @endif"
                    data-id="accessibility.disable_online.status" id="accessibilityOnline_voice"
                    wire:ignore.self>
                    <span></span>
                </div>
            </div>
            <span class="ms-2">غیر فعال سازی موقت نوبت دهی آنلاین</span>
        </div>
        @if (isset($form['accessibility']['disable_online']['status']) &&
                $form['accessibility']['disable_online']['status'] == true)
            <br>
            <div class="d-flex align-items-center">
                <textarea rows="3" class="form-control mt-5 ms-1 " wire:model="form.accessibility.disable_online.message"
                    placeholder=" متن پیغام نمایشی در صورتی غیر فعال بودن "></textarea>
            </div>
        @endif
    </div>
</div>
