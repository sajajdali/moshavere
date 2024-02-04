<div>
    <div class="page-header mb-5">
        <div>
            <h1 class="page-title">
                <span>تنظیمات زمان های حضور</span>
                <strong class="text-primary">{{ $doctor->fullName }}</strong>
            </h1>
        </div>
    </div>
    @include('admin::layouts.components.alert')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <h3>روز های حضور</h3>
                <hr>
                <p>در این قسمت روز هایی که پزشک در مطب حضور دارد را انتخاب و سپس ساعت هار مربوط به هر روز را در آن وارد
                    بکنید!</p>
                <div class="card-body">
                    <form wire:submit='addDayForDoctor' id="setting">
                        @include('appointmentsetting::components.generalsetting.dayofperesent')


                    </form>
                </div>
            </div>
            <div class="text-end">
                <button type="submit" form="setting" class="btn btn-success mt-5">ذخیره</button>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            {{-- section --}}
            <h3>زمان مورد نیاز برای <span class="text-primary">ویزیت</span> هر بیمار</h3>
            <hr style="opacity: 0.5">
            <div class="row">
                <div class="col-md-4 pt-2">
                    <label class="text-primary" for="basic-url">مدت زمان مورد نیاز برای ویزیت هر بیمار</label>
                </div>
                <div class="col-md-8">
                    <div class="input-group mb-3">
                        <input type="number" class="form-control" id="basic-url" aria-describedby="basic-addon3"
                            wire:model='visitTime'>
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon3">مدت زمان به دقیقه</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex  mt-2">
                    <strong class="me-1"> نکته!! </strong>
                    <p class="text-muted">
                        مدت زمان هر نوبت، برای محاسبه تعداد نوبت های هر روز استفاده میشود، برای اینکه در یک روز 8 ساعته
                        8 نوبت داشته باشید، مدت زمان ویزیت را 60 دقیقه تنظیم کنید.
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            {{-- section --}}
            <h3><span class="text-primary">حداقل</span> زمان دریافت نوبت</h3>
            <hr style="opacity: 0.5">
            <div class="row">
                <div class="col-md-5 pt-2">
                    <label class="text-primary" for="basic-url"> زمان دریافت نوبت</label>
                </div>
                <div class="col-md-7">
                    <div class="input-group mb-3">
                        <input type="number" class="form-control" id="basic-url" aria-describedby="basic-addon3"
                            wire:model='maxDayAvaialbe'>
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon3">روز</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex  mt-2">
                    <strong class="me-1"> نکته!! </strong>
                    <p class="text-muted">در این قسمت میتوانید تنظیم بکنید کاربر در زمان دریافت نوبت، نزدیک ترین نوبت را
                        در چه زمانی بتواند دریافت بکند، در صورت قرار دادن عدد 0 کاربر میتواند برای همان رو نوبت دریافت
                        بکند</p>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            {{-- section --}}
            <h3><span class="text-primary">حداکثر</span> زمان دریافت نوبت</h3>
            <hr style="opacity: 0.5">
            <div class="row">
                <div class="col-md-5 pt-2">
                    <label class="text-primary" for="basic-url"> بیمار حداکثر برای چند روز بعد بتواند نوبت دریافت
                        کند</label>
                </div>
                <div class="col-md-7">
                    <div class="input-group mb-3">
                        <input type="number" class="form-control" id="basic-url" aria-describedby="basic-addon3"
                            wire:model='maxDayAvaialbe'>
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon3">روز آینده</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex  mt-2">
                    <strong class="me-1"> نکته!! </strong>
                    <p class="text-muted">در این قسمت میتوانید تعیین کنید که اخرین نوبت تا چند روز آینده برای کاربران
                        قابلدریافت باشد</p>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between">
            <h3>امکان <span class="text-primary">کنسل</span> کردن نوبت </h3>
            <div class="main-toggle-group d-sm-flex align-items-center ms-0">
                <div class="toggle toggle-lg toggle-primary my-1 off" wire:ignore.self
                    data-bs-toggle="collapse" href="#saturdayTimeCollaps"
                    role="button" aria-expanded="false" aria-controls="saturdayTimeCollaps">
                    <span></span>
                </div>
            </div>
        </div>
        <div class="card-body collapse" id="saturdayTimeCollaps">
            {{-- section --}}
            <div class="row">
                <div class="col-md-3 pt-2">
                    <label class="text-primary" for="basic-url">چند روز قبل</label>
                </div>
                <div class="col-md-9">
                    <div class="input-group mb-3">
                        <input type="number" class="form-control" id="basic-url" aria-describedby="basic-addon3"
                            wire:model='maxDayAvaialbe'>
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon3">روز آینده</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex  mt-2">
                    <strong class="me-1"> نکته!! </strong>
                    <p class="text-muted">بیمار از چند روز قبل از فرا رسیدن نوبت خود ، امکان کنسل کردن نوبت خود را داشته باشد!</p>
                </div>
            </div>
        </div>
    </div>

</div>

</div>
@push('scripts')
    <script>
        $(document).ready(function() {
            $('#type_food').on('click', function() {
                @this.set('form.typeFood', $('#type_food').hasClass('on'));
            });
        });
    </script>
@endpush
