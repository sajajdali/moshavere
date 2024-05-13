<div class="mt-5">
    <div class="row">
        <div class="col-md-12 d-flex justify-content-between align-items-center mb-4">
            <h2> اطلاعات پرداخت </h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mx-2">
                <h5>مدت زمان باقی مانده جهت پرداخت صورت حساب</h5>
                <h5 class="badge bg-secondary rounded-pill"> 10 دقیقه</h5>

            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card custom-card rounded-20">
                <div class="card-header  border-bottom d-flex justify-content-between align-items-center">
                    <h4>مبلغ قابل پرداخت</h4>
                    <h4 class="d-flex flex-column">
                        <del class="text-danger mb-1">
                            20,000 هزارتومان
                        </del>
                        <span>
                            5,000 هزارتومان
                        </span>
                    </h4>
                </div>
                <div class="card-body border-bottom">
                    <div class="row">
                        <div class="col-12 mt-2">
                            <span class="text-gray">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                                نوبت به مدت یک ساعت برای شما رزرو میباشد و در صورت عدم پرداخت نوبت شما حذف میشود
                            </span>
                        </div>
                        <div class="col-12 mt-2">
                            <span class="text-gray">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                                نوبت رزرو شده ی شما به معنی نوبت فعال نمیباشد ، برای فعال سازی نوبت ، لطفا اقدام به
                                پرداخت
                                صورت حساب نمایید .
                            </span>
                        </div>
                        @if (setting(Modules\Setting\Enum\SettingKeyEnum::PAYMENT_RULES_AND_CONDITION_STATUS) && setting(Modules\Setting\Enum\SettingKeyEnum::PAYMENT_RULES_AND_CONDITION_STATUS) != false )
                            <div class="col-12 mt-1">
                                <div class="custom-checkbox custom-control">
                                    <input type="checkbox" data-checkboxes="mygroup" checked
                                        class="custom-control-input" id="rulesAndConditionCheckBox">
                                    <label for="rulesAndConditionCheckBox" class="custom-control-label mt-1">
                                        با <a type="button" data-bs-toggle="modal" class="text-primary"
                                            data-bs-target="#exampleModal">شرابط و قوانین </a> پرداخت موافق هستم
                                    </label>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-fotter">
                    <div class="row my-5 mx-5">
                        <div class="col-12">
                            <button id="paymentButton" class="btn btn-primary w-100 ">پرداخت و فعال سازی نوبت</button>
                        </div>
                        <div class="col-12 mt-2 text-center d-none" id="termAndCondtionError">
                            <span class="text-danger">
                             <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                             برای ثبت نوبت باید با شرایط و قوانین مربوط به ثبت نوبت موافقت کنید!
                            </span>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card custom-card rounded-20">
                <a data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false"
                    aria-controls="collapseExample" href="">
                    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                        @if ($fetch['discountCodeApplied'])
                            <h4 class="text-success">
                                کد تخفیف با موفقیت اعمال شد
                            </h4>
                        @else
                            <h4 class="text-primary">
                                کد تخفیف دارید؟
                            </h4>
                        @endif
                    </div>
                </a>
                <div class="collapse" id="collapseExample" wire:ignore.self>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                کد را وارد کنید:
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-8">
                                <div class="form-group">
                                    <input wire:model='discountCode' type="text"
                                        class="form-control   @error('discountCode') is-invalid @enderror"
                                        id="exampleInputEmail2" placeholder="وارد کنید">
                                </div>
                                @error('discountCode')
                                    <span class="text-danger">
                                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                        کد تخفیف وارد شده صحیح نیست
                                    </span>
                                @enderror
                            </div>
                            <div class="col-4">
                                <button wire:click='applyDiscount' wire:loading.class='btn-loading bg-gray'
                                    wire:target='applyDiscount'
                                    class="btn  @if ($fetch['discountCodeApplied']) btn-success  @else btn-info @endif"
                                    @if ($fetch['discountCodeApplied']) disabled @endif>
                                    @if ($fetch['discountCodeApplied'])
                                        <i class="fa fa-check" aria-hidden="true"></i>
                                    @else
                                        ثبت کد
                                    @endif
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card custom-card rounded-20">
                <div class="mt-5 d-flex flex-column flex-start justify-content-between align-items-center">
                    <h4 class="text-primary">اطلاعات نوبت</h4>
                    <hr class="w-100 bg-info opacity-25">
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 d-flex align-items-center">
                            <i class="fa fa-user-md fa-2x text-info" aria-hidden="true"></i>
                        </div>
                        <div class="col-10 d-flex flex-column">
                            <small class=" text-info">پزشک شما</small>
                            <span>دکتر مهرنوش امیری</span>
                            <span class="text-gray"> متخصص زنان</span>
                        </div>
                        <div class="col-12">
                            <hr class="bg-gray opacity-25">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-2 d-flex align-items-center">
                            <i class="fa fa-clock-o fa-2x text-primary" aria-hidden="true"></i>
                        </div>
                        <div class="col-10 d-flex flex-column">
                            <small class=" text-primary">زمان انتخابی</small>
                            <span>25 اردیبهشت </span>
                            <span class="text-gray"> ساعت 15:20</span>
                        </div>
                        <div class="col-12">
                            <hr class="bg-gray opacity-25 mx-3">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-2 d-flex align-items-center">
                            <i class="fa fa-user fa-2x text-secondary" aria-hidden="true"></i>
                        </div>
                        <div class="col-10 d-flex flex-column">
                            <small class=" text-secondary">اطلاعات بیمار</small>
                            <span>آرش خیاری</span>
                            <span class="text-gray"> شماره پرونده: 25</span>
                        </div>
                        <div class="col-12">
                            <hr class="bg-gray opacity-25 mx-3">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- term and condition modal --}}
    <div>
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">شرایط و قوانین</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if (setting(Modules\Setting\Enum\SettingKeyEnum::PAYMENT_RULES_AND_CONDITION_DESCRIPTION))
                            <div id="descriptionContainer">
                                {!! nl2br(setting(Modules\Setting\Enum\SettingKeyEnum::PAYMENT_RULES_AND_CONDITION_DESCRIPTION)) !!}
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/admin.sweetalert.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#rulesAndConditionCheckBox').on('change', function() {
                if ($(this).prop('checked') === false) {
                    $('#paymentButton').prop('disabled', true);
                    $('#termAndCondtionError').removeClass('d-none');
                } else {
                    $('#termAndCondtionError').addClass('d-none');
                    $('#paymentButton').prop('disabled', false);
                }
            });

            var descriptionContainer = $('#descriptionContainer');
            var descriptionText = descriptionContainer.text().trim();
            var sentences = descriptionText.split(/[.!?]+/);

            // Clear the container
            descriptionContainer.empty();

            // Loop through each sentence and add the element at the start
            $.each(sentences, function(index, sentence) {
                if (sentence.trim() !== '') {
                    // Add your element here, for example, a span tag with a class
                    descriptionContainer.append('</br><i class="fa fa-info-circle me-2" aria-hidden="true"></i>' +
                        sentence.trim() + '. ');
                }
            });

        });
    </script>
@endpush
