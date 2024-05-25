<div>
    <div class="page-header mb-5">
        <div>
            <h1 class="page-title">
                <span>تنظیمات زمان های حضور</span>
                <strong class="text-primary">{{ $fetchData['doctor']->fullName }}</strong>
            </h1>
        </div>
    </div>
    @include('admin::layouts.components.alert')

    <div class="card   @if ($errors->has('form.visitType.inPerson') || $errors->has('form.visitType.online')) border border-danger @endif">
        <div class="card-body">
            {{-- section --}}
            <h3> نوع ویزیت </h3>
            <hr style="opacity: 0.5">
            <div class="row">
                @if ($errors->has('form.visitType.inPerson') || $errors->has('form.visitType.online'))
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
        </div>
    </div>
    {{-- manage day of the week  --}}
    <div class="card">
        @include('appointmentsetting::components.generalsetting.dayofperesent')
    </div>
    {{-- special time  --}}
    <div class="card">
        @include('appointmentsetting::components.generalsetting.specialdaytime')
    </div>
    {{-- time for each appointmernt --}}
    <div class="card  @error('form.visitTime') border border-danger @enderror">
        <div class="card-body ">
            {{-- section --}}
            <h3>زمان مورد نیاز برای <span class="text-primary">ویزیت</span> هر بیمار</h3>
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
                        <input type="number" class="form-control @error('form.visitTime') is-invalid @enderror"
                            id="basic-url" aria-describedby="basic-addon3" wire:model='form.visitTime'>
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
        </div>
    </div>
    {{-- min time  --}}
    <div class="card @error('form.minDayAvaialbe') border border-danger @enderror">
        <div class="card-body ">
            {{-- section --}}
            <h3><span class="text-primary">حداقل</span> زمان دریافت نوبت</h3>
            <hr style="opacity: 0.5">
            {{-- TODO::alert Message --}}
            @error('form.minDayAvaialbe')
                <div class="alert alert-danger" role="alert">
                    <p class="text-danger">{{ $message }}
                    </p>
                </div>
            @enderror
            <div class="row">
                <div class="col-md-5 pt-2">
                    <label class="text-primary" for="basic-url"> زمان دریافت نوبت</label>
                </div>
                <div class="col-md-7">
                    <div class="input-group mb-3">
                        <input type="number" class="form-control  @error('form.minDayAvaialbe') is-invalid @enderror "
                            id="basic-url" aria-describedby="basic-addon3" wire:model='form.minDayAvaialbe'>
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon3">روز</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex  mt-2">

                    <p class="text-muted"><strong class="me-1"> نکته!! </strong> در این قسمت میتوانید تنظیم بکنید
                        کاربر در زمان دریافت نوبت، نزدیک ترین نوبت را
                        در چه زمانی بتواند دریافت بکند، در صورت قرار دادن عدد 0 کاربر میتواند برای همان رو نوبت دریافت
                        بکند</p>
                </div>
            </div>
        </div>
    </div>
    {{-- max time  --}}
    <div class="card @error('form.maxDayAvaialbe') border border-danger @enderror">
        <div class="card-body ">
            {{-- section --}}
            <h3><span class="text-primary">حداکثر</span> زمان دریافت نوبت</h3>
            <hr style="opacity: 0.5">
            @error('form.maxDayAvaialbe')
                <div class="alert alert-danger" role="alert">
                    <p class="text-danger">{{ $message }}
                    </p>
                </div>
            @enderror
            <div class="row">
                <div class="col-md-5 pt-2">
                    <label class="text-primary" for="basic-url"> بیمار حداکثر برای چند روز بعد بتواند نوبت دریافت
                        کند</label>
                </div>
                <div class="col-md-7">
                    <div class="input-group mb-3">
                        <input type="number" class="form-control  @error('form.maxDayAvaialbe') is-invalid @enderror"
                            id="basic-url" aria-describedby="basic-addon3" wire:model='form.maxDayAvaialbe'>
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon3">روز آینده</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex  mt-2">
                    <p class="text-muted"><strong class="me-1"> نکته!! </strong> در این قسمت میتوانید تعیین کنید که
                        اخرین نوبت تا چند روز آینده برای کاربران
                        قابلدریافت باشد</p>
                </div>
            </div>
        </div>
    </div>
    {{-- max appointment per day  --}}
    <div class="card  @error('form.maxAvailabeAppointment.*') border border-danger @enderror">
        <div class="card-header border-bottom d-flex justify-content-between">
            <h3>امکان دریافت حداکثر <span class="text-primary">دریافت نوبت</span></h3>
            <div class="main-toggle-group d-sm-flex align-items-center ms-0">
                <div class="toggle toggle-lg toggle-primary my-1 @if (isset($form['maxAvailabeAppointment']['eachDay']) || isset($form['maxAvailabeAppointment']['ForSecretery'])) on @else off @endif customCheckbox"
                    data-id="maxAvailabeAppointment.status" wire:ignore.self data-bs-toggle="collapse"
                    href="#maximumAppointmentCanBePerchased" role="button" aria-expanded="false"
                    aria-controls="maximumAppointmentCanBePerchased">
                    <span></span>
                </div>
            </div>
        </div>
        <div class="collapse @if (isset($form['maxAvailabeAppointment']['eachDay']) || isset($form['maxAvailabeAppointment']['ForSecretery'])) show @endif " id="maximumAppointmentCanBePerchased"
            wire:ignore.self>
            <div class="card-body">
                @error('form.maxAvailabeAppointment.*')
                    <div class="alert alert-danger" role="alert">
                        <p class="text-danger"> لطفا تعداد نوبت را مشخص کنید!!
                        </p>
                    </div>
                @enderror
                {{-- section --}}
                <div class="row">
                    <div class="col-md-3 pt-2">
                        <label class="text-primary" for="basic-url">تعداد نوبت فعال در هر روز</label>
                    </div>
                    <div class="col-md-9 mb-1 mb-3">
                        <div class="input-group ">
                            <input type="number" class="form-control" id="basic-url"
                                aria-describedby="basic-addon3" wire:model='form.maxAvailabeAppointment.eachDay'>
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon3">عدد</span>
                            </div>
                        </div>
                        <span class="text-muted d-flex align-items-center ms-1 mt-1 mb-2"><i
                                class="fa fa-exclamation-circle fa-lg text-light me-1" aria-hidden="true"></i>کاربران
                            در
                            هر روز بتواند چند نوبت دریافت بکند</span>
                    </div>
                    <div class="col-md-3 pt-2">
                        <label class="text-primary" for="basic-url">تعداد نوبت فعال برای منشی</label>
                    </div>
                    <div class="col-md-9 mb-1 mb-3">
                        <div class="input-group ">
                            <input type="number" class="form-control" id="basic-url"
                                aria-describedby="basic-addon3" wire:model='form.maxAvailabeAppointment.ForSecretery'>
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon3">عدد</span>
                            </div>
                        </div>
                        <span class="text-muted d-flex align-items-center ms-1 mt-1 mb-2"><i
                                class="fa fa-exclamation-circle fa-lg text-light me-1" aria-hidden="true"></i>منشی
                            بتواند حداکثر در هر روز چند نوبت ثبت بکند</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- cancel time  --}}
    <div class="card @error('form.cancel.day') border border-danger @enderror">
        <div class="card-header border-bottom d-flex justify-content-between">
            <h3>امکان <span class="text-primary">کنسل</span> کردن نوبت </h3>
            <div class="main-toggle-group d-sm-flex align-items-center ms-0">
                <div class="toggle toggle-lg toggle-primary my-1  customCheckbox @if (isset($form['cancel']['day'])) on  @else off @endif"
                    data-id="cancel.status" wire:ignore.self data-bs-toggle="collapse" href="#cancelCollapseSett"
                    role="button" aria-expanded="false" aria-controls="cancelCollapseSett">
                    <span></span>
                </div>
            </div>
        </div>
        <div class="collapse @if (isset($form['cancel']['day'])) show @endif " id="cancelCollapseSett"
            wire:ignore.self>
            <div class="card-body">
                @error('form.cancel.day')
                    <div class="alert alert-danger" role="alert">
                        <p class="text-danger"> مشخص کنید که از چند روز قبل از فرا رسیدن زمان نوبت امکان کنسل کردن باشد!!
                        </p>
                    </div>
                @enderror
                {{-- section --}}
                <div class="row">
                    <div class="col-md-3 pt-2">
                        <label class="text-primary" for="basic-url">چند روز قبل</label>
                    </div>
                    <div class="col-md-9">
                        <div class="input-group mb-3">
                            <input type="number"
                                class="form-control  @error('form.cancel.day') is-invalid @enderror" id="basic-url"
                                aria-describedby="basic-addon3" wire:model='form.cancel.day'>
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon3">روز آینده</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex  mt-2">

                        <p class="text-muted"><strong class="me-1"> نکته!! </strong> بیمار از چند روز قبل از فرا
                            رسیدن
                            نوبت خود ، امکان کنسل کردن نوبت خود را
                            داشته باشد!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- segments  --}}
    <div class="card @error('form.segments.value') border border-danger @enderror">
        @include('appointmentsetting::components.generalsetting.segments')
    </div>
    {{-- end Date time  --}}
    <div class="card  @error('form.endAppointment.date') border border-danger @enderror">
        <div class="card-header border-bottom d-flex justify-content-between">
            <h3> تعیین پایان تاریخ نوبت دهی </h3>
            <div class="main-toggle-group d-sm-flex align-items-center ms-0">
                <div class="toggle toggle-lg toggle-primary my-1 customCheckbox @if (isset($form['endAppointment']['date'])) on @else off @endif"
                    data-id="endAppointment.status" wire:ignore.self data-bs-toggle="collapse"
                    href="#EndDateTimeCollaps" role="button" aria-expanded="false"
                    aria-controls="EndDateTimeCollaps">
                    <span></span>
                </div>
            </div>
        </div>
        <div class="card-body collapse @if (isset($form['endAppointment']['date'])) show @endif " id="EndDateTimeCollaps"
            wire:ignore.self>
            {{-- section --}}
            @error('form.endAppointment.date')
                <div class="alert alert-danger" role="alert">
                    <p class="text-danger"> لطفا تاریخ را انتخاب کنید!!
                    </p>
                </div>
            @enderror
            <div class="row">
                <div class="col-md-3 pt-2">
                    <label class="text-primary" for="basic-url">انتخاب تاریخ:</label>
                </div>
                <div class="col-md-9">
                    <div class="input-group mb-3">
                        <input type="text" wire:model='form.endAppointment.date'
                            class="form-control @error('form.endAppointment.date') is-invalid @enderror"
                            id="endDatePicker">
                    </div>
                </div>
                <div class="d-flex  mt-2">
                    <p class="text-muted"><strong class="me-1"> نکته!! </strong>
                        تاریخ انتخابی شما اخرین روزی است که نوبت دهی فعال است و نوبت دهی از روز انخابی شما به بعد غیر
                        فعال میشود.
                    </p>
                </div>
            </div>
        </div>
    </div>
    {{-- payment  --}}
    <div class="card @error('form.payment.*') border border-danger @enderror">
        <div class="card-header border-bottom d-flex justify-content-between">
            <h3>پرداخت آنلاین</h3>
            <div class="main-toggle-group d-sm-flex align-items-center ms-0">
                <div class="toggle toggle-lg toggle-primary my-1  customCheckbox
                @if (isset($form['payment']['status']) && $form['payment']['status'] != false) on  @else off @endif"
                    data-id="payment.status" wire:ignore.self data-bs-toggle="collapse" href="#paymentCollaps"
                    role="button" aria-expanded="false" aria-controls="paymentCollaps">
                    <span></span>
                </div>
            </div>
        </div>
        <div class="card-body collapse @if (isset($form['payment']['status']) && $form['payment']['status'] != false) show @endif" id="paymentCollaps"
            wire:ignore.self>
            @error('form.payment.*')
                <div class="alert alert-danger" role="alert">
                    <p class="text-danger"> لطفا مقدار را وارد کنید!!
                    </p>
                </div>
            @enderror
            <div class="row">
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <div class="main-toggle-group d-sm-flex align-items-center ms-0">
                            <div class="toggle toggle-lg toggle-primary my-1  customCheckbox
                            @if (isset($form['payment']['inPerson']['status']) && $form['payment']['inPerson']['status'] == true) on
                                @else
                                off @endif"
                                data-id="payment.inPerson.status" id="sitePaymentStatus" wire:ignore.self>
                                <span></span>
                            </div>
                        </div>
                        <span class="ms-2">فعال بودن پرداخت برای نوبت حضوری</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <div class="main-toggle-group d-sm-flex align-items-center ms-0">
                            <div class="toggle toggle-lg toggle-primary my-1  customCheckbox
                            @if (isset($form['payment']['online']['status']) && $form['payment']['online']['status'] == true) on
                                @else
                                off @endif"
                                data-id="payment.online.status" id="sitePaymentStatus" wire:ignore.self>
                                <span></span>
                            </div>
                        </div>
                        <span class="ms-2">فعال بودن پرداخت برای نوبت آنلاین</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <div class="main-toggle-group d-sm-flex align-items-center ms-0">
                            <div class="toggle toggle-lg toggle-primary my-1 customCheckbox
                            @if (isset($form['payment']['voip']['status']) && $form['payment']['voip']['status'] == true) on
                                @else
                                off @endif"
                                data-id="payment.voip.status" wire:ignore.self id="paymentOnInVoip">
                                <span></span>
                            </div>
                        </div>
                        <span class="ms-2">فعال بودن پرداخت در ویپ</span>
                    </div>
                </div>
            </div>
            <div class="row mt-5" wire:ignore>
                <div id="notPaidStatus">
                    <div class="row">
                        <div class="col-md-5 pt-2">
                            <label class="text-primary" for="paymentStatusSelect"> وضعیت در صورت عدم پرداخت</label>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group">
                                <select name="country"
                                    class="form-control form-select  @error('form.payment.notPayingStatus') is-invalid @enderror"
                                    id="paymentStatusSelect" wire:model='form.payment.notPayingStatus'
                                    data-bs-placeholder="انتخاب کنید...">
                                    <option label="انتخاب کنید..."></option>
                                    <option @if (isset($form['payment']['notPayingStatus']) && $form['payment']['notPayingStatus'] == 'submit') selected @endif value="submit">نوبت ثبت
                                        شود</option>
                                    <option @if (isset($form['payment']['notPayingStatus']) && $form['payment']['notPayingStatus'] == 'dontSubmit') selected @endif value="dontSubmit">نوبت
                                        ثبت نشود</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="inPersonPrice" style="display:@if (isset($form['payment']['inPerson']['status']) && $form['payment']['inPerson']['status'] == true) block @else none @endif ">
                    <div class="row">
                        <div class="col-md-5 pt-2">
                            <label class="text-primary" for="inPersonPriceInpout"> هزینه نوبت حضوری</label>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group">
                                <input type="text"
                                    class="form-control @error('form.payment.inPerson.price') is-invalid @enderror"
                                    id="inPersonPriceInpout" wire:model='form.payment.inPerson.price'
                                    placeholder="مبلغ به تومان">
                            </div>
                        </div>
                    </div>
                </div>
                <div id="onlinePrice" style="display: @if (isset($form['payment']['online']['status']) && $form['payment']['online']['status'] == true) block @else none @endif">
                    <div class="row">
                        <div class="col-md-5 pt-2">
                            <label class="text-primary" for="onlineProceInpit"> هزینه نوبت آنلاین</label>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group">
                                <input type="text"
                                    class="form-control  @error('form.payment.online.price') is-invalid @enderror"
                                    id="onlineProceInpit" wire:model='form.payment.online.price'
                                    placeholder="مبلغ به تومان">
                            </div>
                        </div>
                    </div>
                </div>
                <div id="VoipPrice" style="display: @if (isset($form['payment']['voip']['status']) && $form['payment']['voip']['status'] == true) block  @else none @endif">
                    <div class="row">
                        <div class="col-md-5 pt-2">
                            <label class="text-primary" for="voipPrice"> هزینه نوبت ویپ</label>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group">
                                <input type="text"
                                    class="form-control  @error('form.payment.voip.price') is-invalid @enderror"
                                    id="voipPrice" wire:model='form.payment.voip.price' placeholder="مبلغ به تومان">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- interference  --}}
    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between">
            <h3> عدم کنترل تداخل نوبت ها </h3>
            <div class="main-toggle-group d-sm-flex align-items-center ms-0">
                <div class="toggle toggle-lg toggle-primary my-1 customCheckbox @if (isset($form['interface']['status']) && $form['interface']['status'] == 'true') on @else off @endif"
                    data-id="interference.status" wire:ignore.self data-bs-toggle="collapse"
                    href="#checkForOtherAppointment" role="button" aria-expanded="false"
                    aria-controls="checkForOtherAppointment">
                    <span></span>
                </div>
            </div>
        </div>
        <div class="card-body collapse" id="checkForOtherAppointment" wire:ignore.self>
            {{-- section --}}
            <div class="row">

                <p class="text-muted"><strong class="me-1"> نکته!! </strong> با فعال سازی این قسمت، نوبت های این
                    بخش بدون اینکه با سایر نوبت های همان روز
                    پزشک بررسی شود ، ثبت میشود، به عبارتی ممکن است در یک زمان چند نوبت برای این پزشک ثبت شود </p>
            </div>
        </div>
    </div>
    {{-- MONITORING  --}}
    <div class="card @error('form.monitoring.hour') border border-danger @enderror">
        <div class="card-header border-bottom d-flex justify-content-between">
            <h3><span class="text-primary">پایش</span> نوبت </h3>
            <div class="main-toggle-group d-sm-flex align-items-center ms-0">
                <div class="toggle toggle-lg toggle-primary my-1  customCheckbox @if (isset($form['monitoring']['hour'])) on  @else off @endif"
                    data-id="monitoring.status" wire:ignore.self data-bs-toggle="collapse"
                    href="#monitoringStatusDiv" role="button" aria-expanded="false"
                    aria-controls="monitoringStatusDiv">
                    <span></span>
                </div>
            </div>
        </div>
        <div class="collapse @if (isset($form['monitoring']['hour'])) show @endif " id="monitoringStatusDiv"
            wire:ignore.self>
            <div class="card-body">
                @error('form.monitoring.hour')
                    <div class="alert alert-danger" role="alert">
                        <p class="text-danger"> لطفا مشخص کنید در صورت عدم پرداخت نوبت چند ساعت رزرو بماند!!
                        </p>
                    </div>
                @enderror
                {{-- section --}}
                <div class="row">
                    <div class="col-md-4 pt-2">
                        <label class="text-primary" for="basic-url">مدت زمان انتظار برای پرداخت آنلاین: </label>
                    </div>
                    <div class="col-md-8">
                        <div class="input-group mb-3">
                            <input type="number"
                                class="form-control  @error('form.monitoring.hour') is-invalid @enderror"
                                id="basic-url" placeholder="ساعت پیشنهادی: 24" aria-describedby="basic-addon3"
                                wire:model='form.monitoring.hour'>
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon3">ساعت</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex  mt-2">
                        <p class="text-muted" style="font-size: unset !important">
                            <strong class="me-1"> نکته!! </strong>
                            با فعال کردن این گزینه نوبت ها به صورت اتوماتیک پس از ثبت یا پرداخت آنلاین فعال نمیشوند و پس
                            از دریافت نوبت توسط کاربران، حتما میبایست از طریق مدیریت اقدام به فعال کردن این نوبت ها
                            انجام داد .
                            کاربران پس از ثبت نوبت و دریافت نوبت از طریق سایت، نوبت انها به حالت در انتظار تایید تغییر
                            میکند و پس از تایید مدیریت فعال میشود. در صورتی که پرداخت آنلاین نیز برای این قسمت فعال شده
                            باشد، پس از تایید نوبت توسط مدیریت، کاربران میبایست مبلغ را به صورت انلاین پرداخت کنند تا
                            نوبت انها فعال شود.
                            مدت زمانی که کاربران پس از تایید نوبتشان مهلت دارند تا پرداخت آنلاین را انجام دهند به صورت
                            پیش فرض ۲۴ ساعت میباشد که شما میتوانید این مدت زمان را نیز در قسمت بالا تغییر دهید (فقط در
                            صورت فعال بودن پرداخت آنلاین این زمان را وارد کنید)
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- start Date time  --}}
    <div class="card @if ($errors->has('form.startAppointment.date') || $errors->has('form.startAppointment.time')) border border-danger @endif">
        <div class="card-header border-bottom d-flex justify-content-between">
            <h3> تعیین تاریخ شروع نوبت دهی </h3>
            <div class="main-toggle-group d-sm-flex align-items-center ms-0">
                <div class="toggle toggle-lg toggle-primary my-1 customCheckbox @if (isset($form['startAppointment']['date'])) on @else off @endif"
                    data-id="startAppointment.status" wire:ignore.self data-bs-toggle="collapse"
                    href="#startTimecollaps" role="button" aria-expanded="false" aria-controls="startTimecollaps">
                    <span></span>
                </div>
            </div>
        </div>
        <div class="card-body collapse @if (isset($form['startAppointment']['date'])) show @endif " id="startTimecollaps"
            wire:ignore.self>
            {{-- section --}}
            @if ($errors->has('form.startAppointment.date', 'form.startAppointment.time'))
                <div class="alert alert-danger" role="alert">
                    <p class="text-danger"> لطفا تاریخ و ساعت را انتخاب کنید!!
                    </p>
                </div>
            @endif
            <div class="row">
                <div class="col-md-3 pt-2">
                    <label class="text-primary" for="basic-url">انتخاب تاریخ:</label>
                </div>
                <div class="col-md-9">
                    <div class="input-group mb-3">
                        <input type="text" wire:model='form.startAppointment.date'
                            class="form-control @error('form.startAppointment.date') is-invalid @enderror"
                            id="startDatePicker">
                    </div>
                </div>
                <div class="col-md-3 pt-2">
                    <label class="text-primary" for="basic-url">انتخاب ساعت:</label>
                </div>
                <div class="col-md-9 mb-3">
                    <div class="input-group ">
                        <input type="time" wire:model='form.startAppointment.time'
                            class="form-control @error('form.startAppointment.time') is-invalid @enderror"
                            id="startDatePicker">
                    </div>
                    <small class="text-gray ms-2">برای انتخاب روی آیکون ساعت کلیک کنید ویا مقدار را وارد کنید</small>
                </div>
                <div class="col-12 mt-3 d-flex">
                    <p><strong>نکته:</strong></p> &nbsp;
                    <p>
                        با تعیین این تاریخ ، نوبت دهی قبل از این تاریخ برای کاربران غیر فعال میشود و امکان ثبت نوبت از
                        طریق پنل مدیریت برای منشی وجود دارد.
                    </p>
                </div>
            </div>
        </div>
    </div>
    {{-- add operator  --}}
    <div class="card @error('form.operators.*') border border-danger @enderror">
        @include('appointmentsetting::components.generalsetting.addoperator')
    </div>
    {{-- active status  --}}
    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between">
            <h3> وضعیت فعال بودن </h3>
        </div>
        <div class="card-body " wire:ignore.self>
            {{-- section --}}
            <div class="row">
                <div class="selectgroup selectgroup-pills d-flex align-items-center">
                    <label class="colorinput">
                        <input name="color" type="checkbox" value="azure" class="colorinput-input"
                            wire:model='form.avtive' checked />
                        <span class="colorinput-color bg-azure"> </span>
                    </label>
                    <p class="card-sub-title mt-1 ms-2">فعال</p>
                </div>
            </div>
        </div>
    </div>

    @error('*')
        <div class="alert alert-danger" role="alert">
            <p class="text-danger"><strong>خطا!!</strong> لطفا خطا های بالا را برطرف کنید!</p>
        </div>
    @enderror

    <div class="text-end mb-5 me-3">
        <button type="submit" form="setting" wire:click='saveSetting'
            wire:loading.class='btn-loading disabled btn-gray'
            class="btn btn-success mt-5"><strong>ذخیره</strong></button>

    </div>
</div>
</div>

</div>
@push('styles')
    <style>
        /* Define your styles here */
        p {
            font-size: medium !important;
        }

        label {
            font-size: medium !important;
        }

        h3 {
            font-family: 'Vazir-Regular';
            font-size: 1.4rem;
        }
    </style>
@endpush
@push('scripts')
    <script src="{{ admin_asset('plugins/select2/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            //pass the custom checkboxes values
            $('.customCheckbox').on('click', function() {
                var id = $(this).data('id');
                var inp = $(this);
                @this.set('form.' + id, $(this).hasClass('on'));
                ChangePricesDisplay(id, inp);
            });

            function ChangePricesDisplay(id, inp) {
                if (id == 'payment.inPerson.status' && inp.hasClass('on')) {
                    $('#inPersonPrice').fadeIn();
                } else if (id == 'payment.inPerson.status') {
                    $('#inPersonPrice').fadeOut();
                }
                if (id == 'payment.online.status' && inp.hasClass('on')) {
                    $('#onlinePrice').fadeIn();
                } else if (id == 'payment.online.status') {
                    $('#onlinePrice').fadeOut();
                }
                if (id == 'payment.voip.status' && inp.hasClass('on')) {
                    $('#VoipPrice').fadeIn();
                } else if (id == 'payment.voip.status') {
                    $('#VoipPrice').fadeOut();
                }
            }

            function addPersianDateClassForSpecialDate() {
                $('.specialDate').persianDatepicker({
                    initialValue: false,
                    format: 'L',
                    autoClose: true,
                    onSelect: function(unix) {
                        var specialDateValue = {};
                        $('.specialDate').each(function(key, element) {
                            var dataId = $(element).data('id');
                            var value = $(element).val();
                            specialDateValue[dataId] = value;
                        });
                        @this.set('form.specialDaydateValues', specialDateValue);
                    }
                });
            }
            addPersianDateClassForSpecialDate();
            Livewire.on('loadPersianDatePicker', function() {
                setTimeout(() => {
                    addPersianDateClassForSpecialDate();
                }, 1000);
            });


            $('#endDatePicker').persianDatepicker({
                initialValue: false,
                format: 'L',
                autoClose: true,
                onSelect: function(unix) {
                    @this.set('form.endAppointment.date', $('#endDatePicker').val());
                }
            });
            $('#startDatePicker').persianDatepicker({
                initialValue: false,
                format: 'L',
                autoClose: true,
                onSelect: function(unix) {
                    @this.set('form.startAppointment.date', $('#startDatePicker').val());
                }
            });

            //operator
            setTimeout(() => {
                $('.select2-show-search').select2();
            }, 1000);
            $('.select2-show-search').on('change', function() {
                @this.set('form.operators.ids', $(this).val());
            });

        });
    </script>
@endpush
