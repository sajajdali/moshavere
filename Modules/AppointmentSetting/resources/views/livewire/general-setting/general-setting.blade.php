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

    <div class="card   @if ($errors->has('form.visitType.absente') || $errors->has('form.visitType.online')) border border-danger @endif">
        <div class="card-body">
            {{-- section --}}
            <h3> نوع ویزیت </h3>
            <hr style="opacity: 0.5">
            <div class="row">
                @if ($errors->has('form.visitType.absente') || $errors->has('form.visitType.online'))
                    <div class="alert alert-danger" role="alert">
                        <p class="text-danger"><strong>خطا!!</strong> لطفا نوع ویزیت را تعیین کنید</p>
                    </div>
                @endif
                <div class="col-md-6 mt-3">
                    <div class="main-toggle-group d-flex align-items-center ms-0">
                        <div class="toggle toggle-lg toggle-primary my-1 off customCheckbox" wire:ignore.self
                            data-id="visitType.absente">
                            <span></span>
                        </div>
                        <div class="ms-2">
                            <p class="text-muted m-0">حضوری</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-3">
                    <div class="main-toggle-group d-flex align-items-center ms-0 customCheckbox"
                        data-id="visitType.online">
                        <div class="toggle toggle-lg toggle-primary my-1 off" wire:ignore.self>
                            <span></span>
                        </div>
                        <div class="ms-2">
                            <p class="text-muted m-0">آنلاین</p>
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
                <div class="toggle toggle-lg toggle-primary my-1 off customCheckbox"
                    data-id="maxAvailabeAppointment.status" wire:ignore.self data-bs-toggle="collapse"
                    href="#maximumAppointmentCanBePerchased" role="button" aria-expanded="false"
                    aria-controls="maximumAppointmentCanBePerchased">
                    <span></span>
                </div>
            </div>
        </div>
        <div class="collapse " id="maximumAppointmentCanBePerchased" wire:ignore.self>
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
                    <div class="col-md-9 mb-1">
                        <div class="input-group mb-3">
                            <input type="number" class="form-control" id="basic-url"
                                aria-describedby="basic-addon3" wire:model='form.maxAvailabeAppointment.eachDay'>
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon3">عدد</span>
                            </div>
                        </div>
                        <span class="text-muted d-flex align-items-center"><i
                                class="fa fa-exclamation-circle fa-lg text-light me-1" aria-hidden="true"></i>هر کاربر
                            در
                            هر روز بتواند چند نوتب دریافت بکند</span>
                    </div>
                    <div class="col-md-3 pt-2">
                        <label class="text-primary" for="basic-url">تعداد نوبت فعال در کل</label>
                    </div>
                    <div class="col-md-9">
                        <div class="input-group mb-3">
                            <input type="number" class="form-control" id="basic-url"
                                aria-describedby="basic-addon3" wire:model='form.maxAvailabeAppointment.totall'>
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon3">عدد</span>
                            </div>
                        </div>
                        <span class="text-muted d-flex align-items-center"><i
                                class="fa fa-exclamation-circle fa-lg text-light me-1" aria-hidden="true"></i>هر کاربر
                            بتواند در کل چند نوبت فعال داشته
                            باشد</span>
                    </div>
                    <div class="d-flex  mt-2">
                        <p class="text-muted"> <strong class="me-1"> نکته!! </strong> دقت کنید که حداکثر نوبت
                            دریافتی در
                            یک روز از تعداد کل نوبت ها (فیلد اول نسبت به دوم) بزرگ تر نباشد!</p>
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
                <div class="toggle toggle-lg toggle-primary my-1 off customCheckbox" data-id="cancel.status"
                    wire:ignore.self data-bs-toggle="collapse" href="#saturdayTimeCollaps" role="button"
                    aria-expanded="false" aria-controls="saturdayTimeCollaps">
                    <span></span>
                </div>
            </div>
        </div>
        <div class="card-body collapse" id="saturdayTimeCollaps" wire:ignore.self>
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
                        <input type="number" class="form-control  @error('form.cancel.day') is-invalid @enderror"
                            id="basic-url" aria-describedby="basic-addon3" wire:model='form.cancel.day'>
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon3">روز آینده</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex  mt-2">

                    <p class="text-muted"> <strong class="me-1"> نکته!! </strong> بیمار از چند روز قبل از فرا رسیدن
                        نوبت خود ، امکان کنسل کردن نوبت خود را
                        داشته باشد!</p>
                </div>
            </div>
        </div>
    </div>
    {{-- sunsection time  --}}
    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between">
            <h3>زمان بندی و هزینه بخش ها</h3>
            <div class="main-toggle-group d-sm-flex align-items-center ms-0">
                <div class="toggle toggle-lg toggle-primary my-1 off" wire:ignore.self data-bs-toggle="collapse"
                    href="#sectionTimeTimeCollaps" role="button" aria-expanded="false"
                    aria-controls="sectionTimeTimeCollaps">
                    <span></span>
                </div>
            </div>
        </div>
        <div class="card-body collapse" id="sectionTimeTimeCollaps" wire:ignore.self>
            {{-- section --}}
            <div class="row">
                {{-- TODO:: --}}
            </div>
        </div>
    </div>
    {{-- end Date time  --}}
    <div class="card  @error('form.endAppointment.date') border border-danger @enderror">
        <div class="card-header border-bottom d-flex justify-content-between">
            <h3> تعیین پایان تاریخ نوبت دهی </h3>
            <div class="main-toggle-group d-sm-flex align-items-center ms-0">
                <div class="toggle toggle-lg toggle-primary my-1 off customCheckbox" data-id="endAppointment.status"
                    wire:ignore.self data-bs-toggle="collapse" href="#EndDateTimeCollaps" role="button"
                    aria-expanded="false" aria-controls="EndDateTimeCollaps">
                    <span></span>
                </div>
            </div>
        </div>
        <div class="card-body collapse" id="EndDateTimeCollaps" wire:ignore.self>
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
                        <input type="text"
                            class="form-control @error('form.endAppointment.date') is-invalid @enderror"
                            id="endDatePicker">
                    </div>
                </div>
                <div class="d-flex  mt-2">
                    <p class="text-muted"> <strong class="me-1"> نکته!! </strong> نوبت دهی بهت از تاریخ انتخابی غیر
                        فعال شود </p>
                </div>
            </div>
        </div>
    </div>
    {{-- payment  --}}
    <div class="card @error('form.onlinePayment.*') border border-danger @enderror">
        <div class="card-header border-bottom d-flex justify-content-between">
            <h3>پرداخت آنلاین</h3>
            <div class="main-toggle-group d-sm-flex align-items-center ms-0">
                <div class="toggle toggle-lg toggle-primary my-1 off customCheckbox"
                    data-id="onlinePayment.status" wire:ignore.self data-bs-toggle="collapse"
                    href="#paymentCollaps" role="button" aria-expanded="false" aria-controls="paymentCollaps">
                    <span></span>
                </div>
            </div>
        </div>
        <div class="card-body collapse" id="paymentCollaps" wire:ignore.self>
            @error('form.onlinePayment.*')
            <div class="alert alert-danger" role="alert">
                <p class="text-danger"> لطفا مقدار را وارد کنید!!
                </p>
            </div>
            @enderror
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <div class="main-toggle-group d-sm-flex align-items-center ms-0">
                            <div class="toggle toggle-lg toggle-primary my-1 off customCheckbox"
                                data-id="onlinePayment.online.status"
                                id="sitePaymentStatus" wire:ignore.self>
                                <span></span>
                            </div>
                        </div>
                        <span class="ms-2">فعال بودن پرداخت آنلاین در سایت</span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <div class="main-toggle-group d-sm-flex align-items-center ms-0">
                            <div class="toggle toggle-lg toggle-primary my-1 off customCheckbox"
                                data-id="onlinePayment.voip.status" wire:ignore.self id="paymentOnInVoip">
                                <span></span>
                            </div>
                        </div>
                        <span class="ms-2">فعال بودن پرداخت آنلاین در ویپ</span>
                    </div>
                </div>

            </div>
            <div class="row mt-5" wire:ignore>
                <div class="d-none" id="paymentstatusSelect">
                    <div class="row">
                        <div class="col-md-5 pt-2">
                            <label class="text-primary" for="basic-url"> وضعیت در صورت عدم پرداخت</label>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group">
                                <select name="country" class="form-control form-select  @error('form.onlinePayment.notPayingStatus') is-invalid @enderror" id="default-dropdown"
                                    wire:model='form.onlinePayment.notPayingStatus'
                                    data-bs-placeholder="انتخاب کنید...">
                                    <option label="انتخاب کنید..."></option>
                                    <option value="br">نوبت ثبت شود</option>
                                    <option value="cz">نوبت ثبت نشود</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-none" id="paymentPriceInput">
                    <div class="row">
                        <div class="col-md-5 pt-2">
                            <label class="text-primary" for="basic-url"> مبلغ قابل پرداخت</label>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group">
                                <input type="text" class="form-control  @error('form.onlinePayment.Price') is-invalid @enderror" id="inputName"
                                    wire:model='form.onlinePayment.Price' placeholder="مبلغ به تومان">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- check for other appointment  --}}
    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between">
            <h3> عدم کنترل تداخل نوبت ها </h3>
            <div class="main-toggle-group d-sm-flex align-items-center ms-0">
                <div class="toggle toggle-lg toggle-primary my-1 off customCheckbox" data-id="interference.status"
                    wire:ignore.self data-bs-toggle="collapse" href="#checkForOtherAppointment" role="button"
                    aria-expanded="false" aria-controls="checkForOtherAppointment">
                    <span></span>
                </div>
            </div>
        </div>
        <div class="card-body collapse" id="checkForOtherAppointment" wire:ignore.self>
            {{-- section --}}
            <div class="row">

                <p class="text-muted"> <strong class="me-1"> نکته!! </strong> با فعال سازی این قسمت، نوبت های این
                    بخش بدون اینکه با سایر نوبت های همان روز
                    پزشک بررسی شود ، ثبت میشود، به عبارتی ممکن است در یک زمان چند نوبت برای این پزشک ثبت شود </p>
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
    <script>
        $(document).ready(function() {
            //pass the custom checkboxes values
            $('.customCheckbox').on('click', function() {
                var id = $(this).data('id');
                @this.set('form.' + id, $(this).hasClass('on'));
            });

            function appearPeymentStatusDiv() {
                $('#paymentstatusSelect').fadeIn();
                $('#paymentstatusSelect').removeClass('d-none');
            }

            function appearPeymentPriceDiv() {
                $('#paymentPriceInput').fadeIn();
                $('#paymentPriceInput').removeClass('d-none');
            }

            function fadeOutPeymentStatusDiv() {
                $('#paymentstatusSelect').fadeOut();
                $('#paymentstatusSelect').addClass('d-none');
            }

            function fadeOutPeymentPriceDiv() {
                $('#paymentPriceInput').fadeOut();
                $('#paymentPriceInput').addClass('d-none');
            }
            $('#sitePaymentStatus').click(function(e) {
                if ($('#sitePaymentStatus').hasClass('on')) {
                    appearPeymentStatusDiv();
                    appearPeymentPriceDiv();
                } else {
                    if ($('#paymentOnInVoip').hasClass('on')) {
                        fadeOutPeymentStatusDiv();
                    } else {
                        fadeOutPeymentPriceDiv();
                        fadeOutPeymentStatusDiv();
                    }
                }
            });
            $('#paymentOnInVoip').click(function(e) {
                if ($('#paymentOnInVoip').hasClass('on')) {
                    if ($('#paymentPriceInput').hasClass('d-none')) {
                        appearPeymentPriceDiv();
                    }
                } else {
                    if (!$('#sitePaymentStatus').hasClass('on')) {
                        fadeOutPeymentPriceDiv();
                    }
                }
            });
            $('#endDatePicker').persianDatepicker({
                initialValue: false,
                format: 'L',
                autoClose: true,
                onSelect: function(unix) {
                    @this.set('form.endAppointment.date', $('#endDatePicker').val());
                }
            });
        });
    </script>
@endpush
