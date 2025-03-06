<div>

    <div>
        <div class="page-header mb-5">
            <div>
                <h1 class="page-title">
                    <span>اضافه کردن کد تخفیف</span>
                </h1>
            </div>
        </div>
        @include('admin::layouts.components.alert')
        <div class="card">
            <div class="card-body">
                {{-- seperator --}}
                <div class="row @error('form.code') text-danger @else text-info @enderror ">
                    <div class="col-md-3 d-flex align-items-center">
                        <i class="fa fa-bolt fa-2x me-3" aria-hidden="true"></i>
                        <h5 class="mt-2">کد تخفیف</h5>
                    </div>
                    <div class="col-md-9">
                        <hr style="opacity: 0.75">
                    </div>
                </div>
                <div class="row mb-5">
                    <div class="col-md-12">
                        <label for="discountText" class="form-label">کد تخفیف</label>
                        <input wire:model='form.code' class="form-control  @error('form.code') is-invalid @enderror"
                            id="discountText" type="text">
                    </div>
                    @error('form.code')
                        <div class="text-danger mt-2">
                            <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                {{-- seperator --}}
                <div class="row text-info">
                    <div class="col-md-4 d-flex align-items-center">
                        <i class="fa fa-th-list fa-2x me-3" aria-hidden="true"></i>
                        <h5 class="mt-2">معتبر بودن کد در قسمت های</h5>
                    </div>
                    <div class="col-md-8">
                        <hr style="opacity: 0.75">
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-12" id="selectDoctorSelectBox" wire:ignore>
                        <div class="form-group">
                            <label class="form-label">انتخاب بخش</label>
                            <select multiple class="form-control select2-show-search form-select"
                                id="speciificDocSelect2" data-id="services" data-placeholder="انتخاب کنید...">
                                <option @if ($isEdited && $form['services'] == null) selected @endif value="null">تمام بخش ها
                                </option>
                                @foreach ($fetchData['services'] as $service)
                                    <option @if (isset($form['services']) && in_array($service->id, $form['services'])) selected @endif
                                        value="{{ $service->id }}">{{ $service->title }}</option>
                                @endforeach
                            </select>
                            <small class="text-gray mt-1 ms-2">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                                در صورتی که میخواهید کد تخفیف در یک بخش خاص قابل استفاده باشد آن بخش را انتخاب کنید.
                            </small>
                            @error('form.services')
                                <div class="text-danger">
                                    <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-12" id="selectDoctorSelectBox" wire:ignore>
                        @if (isset($fetchData['doctors']))
                            <div class="form-group">
                                <label class="form-label">انتخاب پزشک</label>
                                <select multiple class="form-control select2-show-search form-select"
                                    id="speciificDocSelect2" data-id="doctors" data-placeholder="انتخاب کنید...">
                                    <option @if ($isEdited && $form['doctors'] == null) selected @endif value="null">تمام پزشکان
                                    </option>
                                    @foreach ($fetchData['doctors'] as $service)
                                        <option @if (isset($form['doctors']) && in_array($service->id, $form['doctors'])) selected @endif
                                            value="{{ $service->id }}">{{ $service->fullName }}</option>
                                    @endforeach
                                </select>
                                <small class="text-gray mt-1 ms-2">
                                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                                    در صورتی که میخواهید کد تخفیف برای یک پزشک قابل استفاده باشد آن پزشک را انتخاب کنید.
                                </small>
                                @error('form.doctors')
                                    <div class="text-danger">
                                        <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        @else
                            <div class="col-md-12 alert alert-info fade show" role="alert">
                                پزشکی وارد نشده است
                            </div>
                        @endif
                    </div>
                </div>
                {{-- seperator --}}
                <div class="row text-info my-3">
                    <div class="col-md-4 d-flex">
                        <i class="fa fa-filter fa-2x me-3" aria-hidden="true"></i>
                        <h5 class="mt-1">مقدار قابل استفاده</h5>
                    </div>
                    <div class="col-md-8">
                        <hr style="opacity: 0.75">
                    </div>
                </div>
                <div class="row  mb-4">
                    <div class="col-12 row mb-2">
                        <div class="col-md-6">
                            <label for="maximum_usage_totall" class="form-label">تعداد قابل استفاده</label>
                            <input wire:model='form.maximum_usage_totall' class="form-control" id="maximum_usage_totall"
                                placeholder="کل تعداد قابل استفاده از کد" type="number">
                            @error('form.maximum_usage_totall')
                                <div class="text-danger">
                                    <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="maximum_usage_each_user" class="form-label">تعداد قابل استفاده برای هر
                                کابر</label>
                            <input wire:model='form.maximum_usage_each_user' class="form-control"
                                id="maximum_usage_each_user" placeholder="تعداد قابل استفاده" type="number">
                            @error('form.form.maximum_usage_each_user')
                                <div class="text-danger">
                                    <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="callTemp" class="form-label">حداقل مبلغ</label>
                        <input wire:model='form.amount.minimum' class="form-control" id="callTemp"
                            placeholder="حداقل مبلغ برای استفاده از این کد" type="number">
                        @error('form.amount.minimum')
                            <div class="text-danger">
                                <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="callTemp" class="form-label">حداکثر مبلغ</label>
                        <input wire:model='form.amount.maximum' class="form-control" id="callTemp"
                            placeholder="حداکثر مبلغ برای استفاده از این کد" type="text">
                        @error('form.amount.maximum')
                            <div class="text-danger">
                                <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
                {{-- seperator --}}
                <div class="row @if ($errors->has('form.payment.type') || $errors->has('form.amount.value')) text-danger @else text-info @endif  mb-3 mt-5">
                    <div class="col-md-4 d-flex">
                        <i class="fa fa-credit-card-alt fa-2x me-3" aria-hidden="true"></i>
                        <h5 class="mt-1">تعیین مبلغ و نوع</h5>
                    </div>
                    <div class="col-md-8">
                        <hr style="opacity: 0.75">
                    </div>
                </div>
                <div class="row  mb-2">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">نوع مبلغ</label>
                            <select id="discountPeymentType" class="form-control  form-select"
                                wire:model='form.payment.type' data-placeholder="انتخاب کنید...">
                                <option selected value="percentage">درصدی</option>
                                <option value="fixed">ثابت</option>
                            </select>
                            @error('form.payment.type')
                                <div class="text-danger">
                                    <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-8">
                        <label for="amountForDiscount" class="form-label">مقدار</label>
                        <input id="amountForDiscount" wire:model='form.payment.value' class="form-control"
                            placeholder="درصد و یا مبلغ تخفیف" type="text">
                        @error('form.payment.value')
                            <div class="text-danger">
                                <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
                {{-- seperator --}}
                <div class="row text-info mb-3 mt-5">
                    <div class="col-md-4 d-flex align-items-center">
                        <i class="fa fa-calendar fa-2x me-3" aria-hidden="true"></i>
                        <h5 class="mt-2">تاریخ فعال بودن</h5>
                    </div>
                    <div class="col-md-8">
                        <hr style="opacity: 0.75">
                    </div>
                </div>
                <div class="row  mb-2">
                    <div class="col-md-6" wire:ignore>
                        <label for="startDateId" class="form-label">تاریخ شروع</label>
                        <input wire:model='form.startDate' data-id="startDate" class="form-control datePicker"
                            data-jdp data-name="form.startDate" id="startDateId" placeholder="انتخاب کنید..." autocomplete="off"
                            type="text">
                    </div>
                    <div class="col-md-6" wire:ignore>
                        <label for="endDateId" class="form-label">تاریخ پایان</label>
                        <input wire:model='form.endDate' data-id="endDate" class="form-control datePicker" data-jdp autocomplete="off"
                            data-name="form.endDate" id="endDateId" placeholder="انتخاب کنید..." type="text">
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-12 mt-5">
                        <div class="checkbox">
                            <div class="custom-checkbox custom-control">
                                <input type="checkbox" data-checkboxes="mygroup" class="custom-control-input"
                                    wire:model='form.active' id="activeCheckbox" checked>
                                <label for="activeCheckbox" class="custom-control-label ">فعال</label>
                            </div>
                        </div>
                    </div>
                    @if ($errors->any())
                        <div class="alert alert-danger mt-4" role="alert">
                            <p class="text-danger"><strong>خطا!!</strong>لطفا اخطارهای بوجود امده را در قسمت بالا برطرف
                                کنید
                            </p>
                        </div>
                    @endif
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary"
                            wire:loading.class="bg-gray btn-loading disabled" wire:click="sotrediscount">
                            @if ($isEdited)
                                ویرایش اطلاعات
                            @else
                                ‌ذخیره اطلاعات
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@push('scripts')
    <script src="{{ admin_asset('plugins/select2/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2-show-search').select2();
            Livewire.on('loadjs', function() {
                setTimeout(() => {
                    $('.select2-show-search').select2();
                }, 200);
            });
            $('.select2-show-search').on('change', function() {
                var data = $(this).data('id');
                var value = $(this).val();
                @this.set('form.' + data, value);
            });
            $('body').on('change', '#discountPeymentType', function() {
                if ($(this).val() == 'percentage') {
                    $('#amountForDiscount').attr('placeholder', 'درصد تخفیف')
                } else {
                    $('#amountForDiscount').attr('placeholder', 'مقدار تخفیف به تومان')

                }
            });

            function js() {
                jalaliDatepicker.startWatch();
                $(document).on('input', '[data-jdp]', function() {
                    let selectedDate = $(this).val();
                    let seterValue = $(this).data('name');
                    @this.set(seterValue, selectedDate);
                });
            }
            js();
            Livewire.on('jsloader', function() {
                setInterval(() => {
                    js();
                }, 1000);
            })

        });
    </script>
@endpush
