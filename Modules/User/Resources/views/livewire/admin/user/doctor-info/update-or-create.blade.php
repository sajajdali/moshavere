<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">
                ویرایش اطلاعات پزشک
            </h1>
        </div>
    </div>

    @include('admin::layouts.components.alert')
    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 col-md-3 mt-3">
                                    <h4 class="text-primary text-start ms-1"><a data-bs-toggle="collapse"
                                            href="#userDataCollaps" role="button" aria-expanded="false"
                                            aria-controls="userDataCollaps" href="">
                                            <i class="fa fa-user me-1" aria-hidden="true"></i>
                                            <span>اطلاعات مربوط به پزشک</span>
                                        </a></h4>
                                </div>
                                <div class="col-12 col-md-9 mt-md-3">
                                    <hr class="my-4">
                                </div>
                                <div class="row ms-2">
                                    <div class="col-sm-12">
                                        <div class="form-group" wire:ignore>
                                            <label class="form-label">تخصصص پزشک</label>
                                            <select wire:model='form.specility' multiple
                                                class="form-control select2-show-search form-select" data-id="specility"
                                                data-placeholder="انتخاب کنید">
                                                @if (isset($this->fetchData['specialities']) && $this->fetchData['specialities']->isNotEmpty())
                                                    <option> انتخاب کنید...</option>
                                                    @foreach ($fetchData['specialities'] as $speciality)
                                                        <option value="{{ $speciality->id }}">{{ $speciality->title }}
                                                        </option>
                                                    @endforeach
                                                @else
                                                    <option value="" disabled>هنوز تخصصی به سایت
                                                        اضافه نشده است</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label" for="default-dropdown">نوع تخصص</label>
                                            <select name="country" class="form-control form-select"
                                                wire:model='form.specialitiesType' id="default-dropdown"
                                                data-bs-placeholder="Select Country">
                                                @if (isset($fetchData['specialitiesType']) && !empty($fetchData['specialitiesType']))
                                                    <option label="انتخاب کنید..."></option>
                                                    @foreach ($fetchData['specialitiesType'] as $specialityType)
                                                        <option value="{{ $specialityType->value }}">
                                                            {{ $specialityType->getName() }}</option>
                                                    @endforeach
                                                @else
                                                    <option label="نوع تخصص وارد نشده است..."></option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="textarea" class="form-label">درباره پزشک</label>
                                            <textarea wire:model='form.biography' class="form-control" maxlength="500" id="textarea" rows="5"></textarea>
                                            <small class="text-gray">میتوانید در این قسمت یک بیوگرافی در مورد پزشک
                                                یادداشت
                                                کنید</small>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="exampleInputEmail2">کد نظام پزشکی</label>
                                            <input wire:model='form.licenceNumber' type="text" class="form-control"
                                                id="exampleInputEmail2" placeholder="وارد کنید">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 mt-5">
                                    <h4 class="text-primary text-start ms-1"><a data-bs-toggle="collapse"
                                            href="#userDataCollaps" role="button" aria-expanded="false"
                                            aria-controls="userDataCollaps" href="">
                                            <i class="fa fa-hospital-o me-1" aria-hidden="true"></i>
                                            <span>بخش و مطب</span>
                                        </a></h4>
                                </div>
                                <div class="col-12 col-md-9 mt-md-5 ">
                                    <hr class="my-4">
                                </div>
                                <div class="row ms-2">
                                    <div class="col-md-12">
                                        <div class="form-group" wire:ignore>
                                            <label class="form-label">بخش های مرتبط با پزشک</label>
                                            <select data-id="services" wire:model='form.services' multiple
                                                class="form-control select2-show-search form-select"
                                                data-placeholder="انتخاب کنید..">
                                                @if (isset($fetchData['services']) && $fetchData['services']->isNotEmpty())
                                                    @foreach ($fetchData['services'] as $services)
                                                        <option value="{{ $services->id }}">{{ $services->title }}
                                                        </option>
                                                    @endforeach
                                                @else
                                                    <option disabled>هنوز بخشی به سیستم اضافه نشده است</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group" wire:ignore>
                                            <label class="form-label">مطب های مرتبط با پزشک</label>
                                            <select wire:model='form.places' multiple
                                                class="form-control select2-show-search form-select" data-id="places"
                                                data-placeholder="انتخاب کنید..">
                                                @if (isset($fetchData['places']) && $fetchData['places']->isNotEmpty())
                                                    @foreach ($fetchData['places'] as $places)
                                                        <option value="{{ $places->id }}">{{ $places->title }}
                                                        </option>
                                                    @endforeach
                                                @else
                                                    <option disabled>هنوز بخشی به سیستم اضافه نشده است</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="doc_status" class="form-label">مدت زمان انتظار برای پزشک</label>
                                            <input wire:model='form.drWaitingTime' class="form-control"  id="doc_status">
                                            <small class="text-gray ms-2">
                                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                                                در صفحه اصلی ، نمایش مدت زمان انتظار برای این پزشک</small>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="doc_rate" class="form-label">درصد رضایت از پزشک</label>
                                            <input wire:model='form.drRate' class="form-control"  id="doc_rate">
                                            <small class="text-gray ms-2">
                                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                                                در پروفایل پزشک</small>
                                        </div>
                                    </div>

                                    {{-- seperator --}}
                                    <div class="col-12 col-md-3 mt-5">
                                        <h4 class="text-primary text-start ms-1"><a data-bs-toggle="collapse"
                                                href="#userDataCollaps" role="button" aria-expanded="false"
                                                aria-controls="userDataCollaps" href="">
                                                <i class="fa fa-user-md me-1" aria-hidden="true"></i>
                                                <span>اطلاعات پروفایل پزشک</span>
                                            </a></h4>
                                    </div>
                                    <div class="col-12 col-md-9 mt-md-5 ">
                                        <hr class="my-4">
                                    </div>
                                    <p class="ms-2">
                                      <strong>نکته:</strong>
                                        اطلاعات مربوط به این قسمت ، در صفحه ی پروفایل پزشک ، در سایت به بیماران نمایش داده می شود.
                                    </p>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="address_textarea" class="form-label">آدرس</label>
                                                <input wire:model='form.dr_display_address' class="form-control" id="address_textarea">
                                                <small class="text-gray ms-2">
                                                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                                                فقط تقاطع آخر(برای مثال: چهاراه جهان کودک)</small>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="dr_display_mobile" class="form-label">شماره تماس</label>
                                                <input wire:model='form.dr_display_mobile' class="form-control" id="dr_display_mobile">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="drDisplayNavigation" class="form-label">لینک مکان یابی</label>
                                                <input wire:model='form.drDisplayNavigation' class="form-control" id="drDisplayNavigation">
                                                <small class="text-gray ms-2">
                                                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                                                    لینک مسیریابی به لوکیشن، گرفته شده از یکی از مسیریاب ها</small>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="drDisplayExperince" class="form-label">سابقه پزشک</label>
                                                <input wire:model='form.drDisplayExperince' class="form-control" id="drDisplayExperince">
                                                <small class="text-gray ms-2">
                                                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                                                    برای مثال: 26سال تجربه</small>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="drDisplayExperince" class="form-label">بنر</label>
                                            <div class="input-group ">
                                                <span class="input-group-btn">
                                                    <button class="btn btn-primary select_file"
                                                        data-bs-target="#file-selector-modal" data-bs-toggle="modal" type="button">
                                                        <i class="fa fa-picture-o"></i>
                                                        انتخاب تصویر
                                                    </button>
                                                </span>
                                                <input id="thumbnail" class="form-control" type="text" name="filepath" wire:model="form.drBanner">
                                            </div>
                                            <small class="text-gray ms-2">
                                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                                                برای اپلیکیشن</small>
                                        </div>
                                        @unless(disableUi())
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="drDisplayDiscription" class="form-label">توضیحات مربوط به پزشک</label>
                                                <textarea wire:model='form.drDisplayDiscription' rows="3" maxlength="500" class="form-control" id="drDisplayDiscription"></textarea>
                                                <small class="text-gray ms-2">
                                                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                                                    توضیحاتی که مختص به دریافت نوبت از این پزشک می باشد و در صفحه ی پروفایل پزشک نمایش داده می شود</small>
                                            </div>
                                        </div>
                                        @endunless
                                    </div>
                                    {{-- seperator --}}
                                    <div class="col-12 col-md-3 mt-5">
                                        <h4 class="text-primary text-start ms-1"><a data-bs-toggle="collapse"
                                                href="#userDataCollaps" role="button" aria-expanded="false"
                                                aria-controls="userDataCollaps" href="">
                                                <i class="fa fa-sort me-1" aria-hidden="true"></i>
                                                <span>ترتیب و وضعیت نمایش </span>
                                            </a></h4>
                                    </div>
                                    <div class="col-12 col-md-9 mt-md-5 ">
                                        <hr class="my-4">
                                    </div>
                                    {{-- seperator --}}
                                    <div class="col-md-12 mt-3">
                                        <div class="form-group">
                                            <label for="order-id">ترتیب نمایش در لیست پزشکان</label>
                                            <input wire:model='form.order' type="number" class="form-control"
                                                id="order-id" placeholder="به عدد">
                                        </div>
                                    </div>
                                    {{-- emergency status --}}
                                    @unless(disableUi())
                                    <div class="col-md-12 mt-2">
                                        <div class="d-flex flex-wrap align-items-center">
                                            <div class="material-switch">
                                                <input class="listOrderCheckBox" data-id="orderForEmergencyVisitDiv"
                                                    wire:model='form.showDocInEmergencyVisit.status'
                                                    id="showDocInEmergencyVisit" type="checkbox" checked />
                                                <label for="showDocInEmergencyVisit" class="label-info"></label>
                                            </div>
                                            <p class="card-sub-title">نمایش در لیست {{setting(\Modules\Setting\Enum\SettingKeyEnum::SITE_FIRST_SECTION_TITLE) ?? 'اول در صفحه اصلی'}}</p>
                                        </div>
                                    </div>
                                    @endunless
                                    <div class="col-md-12" id="orderForEmergencyVisitDiv" wire:ignore.self>
                                        <div class="form-group">
                                            <label for="order-showDocInEmergencyVisit">ترتیب نمایش در در {{setting(\Modules\Setting\Enum\SettingKeyEnum::SITE_FIRST_SECTION_TITLE) ?? ' اول در صفحه اصلی'}}
                                                </label>
                                            <input wire:model='form.showDocInEmergencyVisit.order' type="number"
                                                class="form-control" id="order-showDocInEmergencyVisit"
                                                placeholder="به عدد">
                                        </div>
                                    </div>
                                    {{-- info status --}}
                                    @unless(disableUi())
                                    <div class="col-md-12 mt-2">
                                        <div class="d-flex flex-wrap align-items-center">
                                            <div class="material-switch">
                                                <input class="listOrderCheckBox" data-id="orderInDoctorsInfo"
                                                    wire:model='form.ShowInIntrodocs.status' id="OrderStatusInDocInfo"
                                                    type="checkbox" checked />
                                                <label for="OrderStatusInDocInfo" class="label-info"></label>
                                            </div>
                                            <p class="card-sub-title">نمایش در لیست {{setting(\Modules\Setting\Enum\SettingKeyEnum::SITE_SECEND_SECTION_TITLE) ?? 'دوم در صفحه اصلی'}}</p>
                                        </div>
                                    </div>
                                    @endunless
                                    <div class="col-md-12" id="orderInDoctorsInfo" wire:ignore.self>
                                        <div class="form-group">
                                            <label for="order-showDocInEmergencyVisit">ترتیب نمایش در {{setting(\Modules\Setting\Enum\SettingKeyEnum::SITE_SECEND_SECTION_TITLE) ?? 'دوم در صفحه اصلی'}}</label>
                                            <input wire:model='form.ShowInIntrodocs.order' type="number"
                                                class="form-control" id="order-showDocInEmergencyVisit"
                                                placeholder="به عدد">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="d-flex flex-wrap align-items-center">
                                            <div class="material-switch">
                                                <input wire:model='form.active' id="uncheckedInfoSwitch"
                                                    name="siwtch04" type="checkbox" checked />
                                                <label for="uncheckedInfoSwitch" class="label-info"></label>
                                            </div>
                                            <p class="card-sub-title">فعال بودن نوبت دهی</p>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="d-flex flex-wrap align-items-center">
                                            <div class="material-switch">
                                                <input wire:model='form.drStoreAppSms' id="drStoreAppSms" name="drStoreAppSms"
                                                    type="checkbox" />
                                                <label for="drStoreAppSms" class="label-danger"></label>
                                            </div>
                                            <p class="card-sub-title">عدم ارسال پیامک به پزشک بعد از دریافت نوبت</p>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="d-flex flex-wrap align-items-center">
                                            <div class="material-switch">
                                                <input wire:model='form.banUser' id="banUser" name="siwtch04"
                                                    type="checkbox" />
                                                <label for="banUser" class="label-danger"></label>
                                            </div>
                                            <p class="card-sub-title">مسدود کردن کاربر</p>
                                        </div>
                                    </div>
                                </div>
                                @if(\Modules\OnlineConsultation\Support\ConsultationAccess::enabled())
                                <div class="col-12 mt-4">
                                    <div class="card border-primary shadow-sm">
                                        <div class="card-header bg-primary text-white d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-headset" aria-hidden="true"></i>
                                            <h3 class="card-title mb-0 text-white">تنظیمات مشاور آنلاین</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check form-switch mb-3 d-flex align-items-center gap-3" style="padding-right: 0;">
                                                <input class="form-check-input" type="checkbox" role="switch" id="onlineConsultant" wire:model.live="onlineConsultant">
                                                <label class="form-check-label fw-bold" style="margin-right: 10px;" for="onlineConsultant">مشاور آنلاین است</label>
                                            </div>
                                            <small class="text-muted d-block mb-3">با فعال‌کردن این گزینه، این پزشک در داشبورد مشاوره آنلاین قرار می‌گیرد.</small>
                                            @if($onlineConsultant)
                                                <div class="row g-3 border-top pt-3">
                                                    <div class="col-md-6"><label class="form-label" for="consultationExtension">داخلی <span class="text-danger">*</span></label><input class="form-control" id="consultationExtension" wire:model="consultationExtension" inputmode="numeric" dir="ltr" placeholder="مثلاً ۲۰۱"></div>
                                                    <div class="col-md-6"><label class="form-label" for="consultationHourlyRate">هزینه یک ساعت مشاوره (تومان) <span class="text-danger">*</span></label><input class="form-control" id="consultationHourlyRate" wire:model="consultationHourlyRate" type="number" min="0" max="1000000000" dir="ltr" placeholder="مثلاً ۱۰۰۰۰۰۰"><small class="text-muted">این مبلغ هنگام ثبت نوبت ذخیره می‌شود و تغییر بعدی نرخ، نوبت قبلی را تغییر نمی‌دهد.</small></div>
                                                    <div class="col-12"><div class="alert alert-light border mb-0"><i class="fa-solid fa-circle-info text-primary me-1" aria-hidden="true"></i>آدرس سرور ویپ از بخش <a href="{{ route('admin.consultation.settings') }}" target="_blank" rel="noopener">تنظیمات VoIP مشاوره آنلاین</a> خوانده می‌شود و برای همه مشاوران سایت اعمال می‌شود. برای تغییر آن، همان‌جا ویرایش کنید.</div></div>
                                                    <div class="col-md-6"><label class="form-label" for="consultationVoipUsername">یوزر ویپ</label><input class="form-control" id="consultationVoipUsername" wire:model="consultationVoipUsername" dir="ltr"></div>
                                                    <div class="col-md-6"><label class="form-label" for="consultationVoipSecret">پسورد ویپ</label><input class="form-control" id="consultationVoipSecret" type="password" wire:model="consultationVoipSecret" dir="ltr" autocomplete="new-password" placeholder="برای حفظ پسورد قبلی خالی بگذارید"></div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif
                                <div class="col-12 text-end mt-4">
                                    <button wire:click='storeDocInfo' wire:loading.class='btn-loading btn-gray'
                                        class="btn btn-success">ذخیره اطلاعات</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <livewire:admin::file-manager-modal />
</div>
@push('scripts')
    <script src="{{ admin_asset('plugins/select2/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2-show-search').select2();

            $('.select2-show-search').change(function() {
                var value = $(this).val();
                var id = $(this).data('id');
                @this.set('form.' + id, value);
            });
            $('.listOrderCheckBox').each(function() {
                var inputOrderId = $(this).data('id');
                if ($(this).prop('checked') == true) {
                    $('#' + inputOrderId).css({
                        display: 'block',
                    });
                } else {
                    $('#' + inputOrderId).css({
                        display: 'none',
                    });
                }
            });
            $('body').on('change', '.listOrderCheckBox', function() {
                var checked = $(this).prop('checked');
                var inputOrderId = $(this).data('id');
                if (checked) {
                    $('#' + inputOrderId).fadeIn();
                } else {
                    $('#' + inputOrderId).fadeOut();
                }
            });
            Livewire.on('select_file', (param) => {
                @this.set('form.drBanner' , param.url);
                $('#file-selector-modal').modal('hide');
            });
        });
    </script>
@endpush
