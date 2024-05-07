<div>

    <div>
        <div class="page-header mb-5">
            <div>
                <h1 class="page-title">
                    <span>اضافه کردن مطب</span>
                </h1>
            </div>
        </div>
        @include('admin::layouts.components.alert')
        <div class="card">
            <div class="card-body">
                {{-- seperator --}}
                <div class="row text-info">
                    <div class="col-md-4 d-flex align-items-center">
                        <i class="fa fa-th-list fa-2x me-3" aria-hidden="true"></i>
                        <h5>معتبر بودن کد در قسمت های</h5>
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
                                <option value="null">تمام بخش ها</option>
                                @foreach ($fetchData['services'] as $service)
                                    <option value="{{ $service->id }}">{{ $service->title }}</option>
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
                        <div class="form-group">
                            <label class="form-label">انتخاب بخش</label>
                            <select multiple class="form-control select2-show-search form-select"
                                id="speciificDocSelect2" data-id="doctors" data-placeholder="انتخاب کنید...">
                                <option value="null">تمام پزشکان</option>
                                @foreach ($fetchData['doctors'] as $service)
                                    <option value="{{ $service->id }}">{{ $service->fullName }}</option>
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
                    </div>
                </div>
                {{-- seperator --}}
                <div class="row text-info my-3">
                    <div class="col-md-4 d-flex">
                        <i class="fa fa-filter fa-2x me-3" aria-hidden="true"></i>
                        <h5>مقدار قابل استفاده</h5>
                    </div>
                    <div class="col-md-8">
                        <hr style="opacity: 0.75">
                    </div>
                </div>
                <div class="row  mb-2">
                    <div class="col-md-6">
                        <label for="callTemp" class="form-label">حداقل مبلغ</label>
                        <input wire:model='form.amount.minimum' class="form-control" id="callTemp"
                            placeholder="حداقل مبلغ برای استفاده از این کد" type="text">
                        @error('form.amount.minimum')
                            <div class="text-danger">
                                <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="callTemp" class="form-label">حداکثر مبلغ</label>
                        <input wire:model='form.amount.maximum' class="form-control" id="callTemp"
                            placeholder="حداکثر مبلغ برای استفاده از این کد" type="text">
                        @error('form.amount.maximum')
                            <div class="text-danger">
                                <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
                <div class="row text-info mt-5">
                    <div class="col-md-4 d-flex">
                        <i class="fa fa-credit-card-alt fa-2x me-3" aria-hidden="true"></i>
                        <h5>تعیین مبلغ و نوع</h5>
                    </div>
                    <div class="col-md-8">
                        <hr style="opacity: 0.75">
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-12 mt-5">
                        <div class="checkbox">
                            <div class="custom-checkbox custom-control">
                                <input type="checkbox" data-checkboxes="mygroup" class="custom-control-input"
                                    wire:model='form.active' id="activeCheckbox">
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
                        <button type="submit" class="btn btn-primary" wire:loading.class="bg-gray btn-loading disabled"
                            wire:click="sotrediscount">
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
        });
    </script>
@endpush
