<div>
    <style>
        #RegistrAnAppointment .modal-dialog { max-width: 760px; max-height: calc(100vh - 32px); }
        #RegistrAnAppointment .modal-content { max-height: calc(100vh - 32px); border: 0; border-radius: 22px; overflow: hidden; box-shadow: 0 24px 70px rgba(31, 41, 55, .18); }
        #RegistrAnAppointment .appointment-modal-head { padding: 22px 26px 18px; color: #fff; background: linear-gradient(135deg, #1665d8 0%, #7257d9 100%); }
        #RegistrAnAppointment .appointment-modal-head h5 { color: inherit; font-size: 1.15rem; margin: 0; }
        #RegistrAnAppointment .appointment-title { display: flex; align-items: center; gap: 10px; }
        #RegistrAnAppointment .appointment-modal-head p { color: rgba(255,255,255,.78); margin: 6px 0 0; font-size: .84rem; }
        #RegistrAnAppointment .appointment-close { width: 34px; height: 34px; border: 0; border-radius: 10px; color: #fff; background: rgba(255,255,255,.14); }
        #RegistrAnAppointment .appointment-progress { display: flex; gap: 7px; margin-top: 18px; }
        #RegistrAnAppointment .appointment-progress span { height: 4px; flex: 1; border-radius: 99px; background: rgba(255,255,255,.22); }
        #RegistrAnAppointment .appointment-progress span.active { background: #fff; }
        #RegistrAnAppointment .modal-body { min-height: 0; padding: 24px 26px 28px; overflow-y: auto !important; overscroll-behavior: contain; background: #fbfcff; }
        #RegistrAnAppointment .appointment-modal-head, #RegistrAnAppointment .modal-footer { flex-shrink: 0; }
        #RegistrAnAppointment .appointment-card { padding: 18px; margin-bottom: 16px; border: 1px solid #e7ebf3; border-radius: 16px; background: #fff; }
        #RegistrAnAppointment .section-title { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; color: #172033; font-weight: 700; }
        #RegistrAnAppointment .section-icon { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 10px; color: #356ad8; background: #edf3ff; }
        #RegistrAnAppointment .form-control, #RegistrAnAppointment .form-select { min-height: 46px; border-color: #dfe5ef; border-radius: 11px; background-color: #fff; }
        #RegistrAnAppointment .form-control:focus, #RegistrAnAppointment .form-select:focus { border-color: #7299eb; box-shadow: 0 0 0 3px rgba(62,110,218,.11); }
        #RegistrAnAppointment .field-hint { color: #8490a5; font-size: .78rem; }
        #RegistrAnAppointment .kind-grid, #RegistrAnAppointment .option-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        #RegistrAnAppointment .option-grid.two { grid-template-columns: repeat(2, 1fr); }
        #RegistrAnAppointment .choice-input { position: absolute; opacity: 0; pointer-events: none; }
        #RegistrAnAppointment .choice-card { display: flex; align-items: center; gap: 11px; min-height: 64px; padding: 11px 13px; margin: 0; cursor: pointer; border: 1px solid #e1e6ef; border-radius: 13px; background: #fff; transition: .16s ease; }
        #RegistrAnAppointment .choice-card:hover { border-color: #aebfe5; transform: translateY(-1px); }
        #RegistrAnAppointment .choice-input:checked + .choice-card { border-color: #4779df; background: #f3f7ff; box-shadow: 0 0 0 2px rgba(71,121,223,.1); }
        #RegistrAnAppointment .choice-icon { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 38px; width: 38px; height: 38px; border-radius: 11px; color: #3267d4; background: #eaf1ff; }
        #RegistrAnAppointment .choice-copy strong, #RegistrAnAppointment .choice-copy small { display: block; }
        #RegistrAnAppointment .choice-copy small { margin-top: 2px; color: #8a94a6; font-size: .72rem; }
        #RegistrAnAppointment .selected-kind { display: flex; align-items: center; gap: 9px; padding: 10px 13px; margin-bottom: 16px; border-radius: 12px; color: #2859bd; background: #edf4ff; font-size: .84rem; }
        #RegistrAnAppointment .advanced-toggle { color: #56647a; text-decoration: none; font-weight: 600; }
        #RegistrAnAppointment .modal-footer { padding: 16px 26px 22px; border: 0; background: #fbfcff; }
        #RegistrAnAppointment .primary-action { min-width: 135px; min-height: 44px; border: 0; border-radius: 12px; background: linear-gradient(135deg, #216cda, #6657d8); box-shadow: 0 8px 18px rgba(58,91,198,.22); }
        #RegistrAnAppointment .secondary-action { min-height: 44px; border-radius: 12px; }
        #RegistrAnAppointment .lookup-icon { display: flex; align-items: center; justify-content: center; width: 54px; height: 54px; margin: 2px auto 15px; border-radius: 16px; color: #356ad8; background: #edf3ff; font-size: 1.35rem; }
        #RegistrAnAppointment .patient-summary { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 11px 14px; margin-bottom: 16px; border: 1px solid #e4eaf4; border-radius: 13px; background: #fff; }
        #RegistrAnAppointment .error-message { display: block; margin-top: 6px; color: #dc3545; font-size: .8rem; }
        #RegistrAnAppointment .mobile-field { display: flex; direction: rtl; width: 100%; }
        #RegistrAnAppointment .mobile-field .mobile-icon { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 48px; border: 1px solid #dfe5ef; border-left: 0; border-radius: 0 11px 11px 0; color: #356ad8; background: #f4f7fc; font-size: 1.15rem; }
        #RegistrAnAppointment .mobile-field .form-control { direction: ltr; text-align: left; border-radius: 11px 0 0 11px; }
        #RegistrAnAppointment .mobile-field .form-control:focus { position: relative; z-index: 1; }
        @media (max-width: 767px) {
            #RegistrAnAppointment .modal-dialog { margin: 10px; }
            #RegistrAnAppointment .modal-body, #RegistrAnAppointment .modal-footer, #RegistrAnAppointment .appointment-modal-head { padding-left: 17px; padding-right: 17px; }
            #RegistrAnAppointment .kind-grid, #RegistrAnAppointment .option-grid, #RegistrAnAppointment .option-grid.two { grid-template-columns: 1fr; }
        }
    </style>

    <div wire:ignore.self class="modal fade" id="RegistrAnAppointment" data-bs-backdrop="static"
        data-bs-keyboard="false" tabindex="-1" aria-labelledby="appointmentRegistrationTitle" aria-hidden="true" dir="rtl">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="appointment-modal-head">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <h5 class="appointment-title" id="appointmentRegistrationTitle"><i class="fa fa-calendar-check-o"></i><span>ثبت سریع نوبت</span></h5>
                            <p>@if ($step === 1) ابتدا مراجعه‌کننده را پیدا کنید @elseif ($step === 2) مشخصات نوبت را بررسی و ثبت کنید @elseif ($step === 3) اپراتور پاسخ‌گو را انتخاب کنید @else تأیید ثبت نوبت هم‌زمان @endif</p>
                        </div>
                        <button type="button" class="appointment-close" wire:click="dismisModal" data-bs-dismiss="modal" aria-label="بستن"><i class="fa fa-times"></i></button>
                    </div>
                    <div class="appointment-progress" aria-hidden="true">
                        <span class="active"></span><span class="{{ $step >= 2 ? 'active' : '' }}"></span>
                        @if (isset($fetchData['operators']))<span class="{{ $step >= 3 ? 'active' : '' }}"></span>@endif
                    </div>
                </div>

                <div class="modal-body">
                    @error('userNotExists')
                        <div class="alert alert-danger border-0 rounded-3" role="alert"><i class="fa fa-exclamation-triangle ms-1"></i>{{ $message }}</div>
                    @enderror

                    @if ($step === 1)
                        <div class="lookup-icon"><i class="fa fa-user-plus"></i></div>
                        <div class="text-center mb-4"><strong class="d-block mb-1">جست‌وجوی مراجعه‌کننده</strong><span class="field-hint">با شماره موبایل سریع‌تر جست‌وجو کنید</span></div>
                        <div class="appointment-card">
                            <label for="appointment_mobile" class="form-label fw-bold">شماره موبایل</label>
                            <div class="mobile-field" x-data>
                                <span class="mobile-icon" aria-hidden="true"><i class="fa fa-mobile"></i></span>
                                <input type="tel" inputmode="numeric" autocomplete="tel" autofocus maxlength="11"
                                    class="form-control @error('form.number') is-invalid @enderror" id="appointment_mobile"
                                    value="{{ $form['number'] }}" wire:keydown.enter.prevent="numberSet" placeholder="09123456789"
                                    x-on:input="
                                        let number = $el.value
                                            .replace(/[۰-۹]/g, digit => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
                                            .replace(/[٠-٩]/g, digit => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit))
                                            .replace(/[^0-9]/g, '')
                                            .slice(0, 11);
                                        $el.value = number;
                                        $wire.set('form.number', number, false);
                                        if (number.length === 11) { $wire.numberSet(); }
                                    ">
                            </div>
                            @error('form.number')<span class="error-message">{{ $message }}</span>@enderror

                            @if ($fetchData['document_number_enabled'] ?? false)
                                <div class="d-flex align-items-center gap-3 my-3"><hr class="flex-grow-1 m-0"><span class="field-hint">یا</span><hr class="flex-grow-1 m-0"></div>
                                <label for="appointment_document" class="form-label fw-bold">شماره پرونده</label>
                                <input type="text" inputmode="numeric" autocomplete="off" class="form-control @error('form.document_number') is-invalid @enderror" id="appointment_document" wire:model="form.document_number" placeholder="شماره پرونده را وارد کنید">
                                @error('form.document_number')<span class="error-message">{{ $message }}</span>@enderror
                            @endif
                        </div>
                    @elseif ($step === 2)
                        @if (isset($fetchData['user']))
                            <div class="patient-summary"><div><i class="fa fa-check-circle text-success ms-2"></i><strong>{{ $form['first_name'] }} {{ $form['last_name'] }}</strong></div><span class="field-hint">{{ $form['number'] ?? $fetchData['user']->mobile }}</span></div>
                        @endif

                        @if (count($fetchData['appointment_kinds'] ?? []) === 0)
                            <div class="alert alert-danger border-0 rounded-3">برای این برنامه هیچ نوع نوبتی فعال نشده است. ابتدا تنظیمات نوبت را اصلاح کنید.</div>
                        @elseif (count($fetchData['appointment_kinds'] ?? []) > 1)
                            <div class="appointment-card">
                                <div class="section-title"><span class="section-icon"><i class="fa fa-stethoscope"></i></span>نوع نوبت را انتخاب کنید</div>
                                <div class="kind-grid">
                                    @foreach ($fetchData['appointment_kinds'] as $kind)
                                        <div>
                                            <input class="choice-input" type="radio" wire:model="form.kind" value="{{ $kind['value'] }}" name="appointment_kind" id="appointment_kind_{{ $kind['value'] }}">
                                            <label class="choice-card" for="appointment_kind_{{ $kind['value'] }}">
                                                <span class="choice-icon"><i class="fa {{ $kind['icon'] }}"></i></span>
                                                <span class="choice-copy"><strong>{{ $kind['name'] }}</strong><small>{{ $kind['hint'] }}</small></span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('form.kind')<span class="error-message">{{ $message }}</span>@enderror
                            </div>
                        @elseif (count($fetchData['appointment_kinds'] ?? []) === 1)
                            <div class="selected-kind"><i class="fa {{ $fetchData['appointment_kinds'][0]['icon'] }}"></i>این نوبت به‌صورت <strong>{{ $fetchData['appointment_kinds'][0]['name'] }}</strong> ثبت می‌شود.</div>
                        @endif

                        <div class="appointment-card">
                            <div class="section-title"><span class="section-icon"><i class="fa fa-user"></i></span>مشخصات مراجعه‌کننده</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="appointment_first_name" class="form-label">نام</label>
                                    <input type="text" class="form-control @error('form.first_name') is-invalid @enderror" id="appointment_first_name" wire:model="form.first_name">
                                    @error('form.first_name')<span class="error-message">{{ $message }}</span>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="appointment_last_name" class="form-label">نام خانوادگی</label>
                                    <input type="text" class="form-control @error('form.last_name') is-invalid @enderror" id="appointment_last_name" wire:model="form.last_name">
                                    @error('form.last_name')<span class="error-message">{{ $message }}</span>@enderror
                                </div>
                                <div class="col-12">
                                    <label for="appointment_document_registration" class="form-label">شماره پرونده <span class="field-hint">(اختیاری)</span></label>
                                    <input type="text" class="form-control" id="appointment_document_registration" wire:model="form.document_number">
                                </div>
                            </div>
                        </div>

                        <div class="appointment-card">
                            <div class="section-title"><span class="section-icon"><i class="fa fa-clock-o"></i></span>زمان نوبت</div>
                            <div class="row g-3">
                                <div class="col-md-6"><label for="appointment_from" class="form-label">از ساعت</label><input type="time" class="form-control @error('form.time.from') is-invalid @enderror" id="appointment_from" wire:model="form.time.from">@error('form.time.from')<span class="error-message">{{ $message }}</span>@enderror</div>
                                <div class="col-md-6"><label for="appointment_until" class="form-label">تا ساعت</label><input type="time" class="form-control @error('form.time.until') is-invalid @enderror" id="appointment_until" wire:model="form.time.until">@error('form.time.until')<span class="error-message">{{ $message }}</span>@enderror</div>
                            </div>
                        </div>

                        <div class="appointment-card">
                            <a class="advanced-toggle d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#appointmentAdvancedOptions" role="button" aria-expanded="false" aria-controls="appointmentAdvancedOptions">
                                <span><i class="fa fa-sliders ms-2"></i>تنظیمات تکمیلی</span><i class="fa fa-angle-down"></i>
                            </a>
                            <div class="collapse mt-3 {{ $errors->has('form.registerWithoutPayment') ? 'show' : '' }}"
                                id="appointmentAdvancedOptions" x-data
                                x-init="$el.addEventListener('shown.bs.collapse', () => setTimeout(() => $el.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 50))">
                                <label class="form-label">جایگاه نوبت</label>
                                <div class="option-grid two mb-3">
                                    <div><input class="choice-input" type="radio" wire:model="form.appType" value="main_app" name="appointment_type" id="appointment_main"><label class="choice-card" for="appointment_main"><span class="choice-icon"><i class="fa fa-list-ol"></i></span><span class="choice-copy"><strong>نوبت اصلی</strong></span></label></div>
                                    <div><input class="choice-input" type="radio" wire:model="form.appType" value="in_between" name="appointment_type" id="appointment_between"><label class="choice-card" for="appointment_between"><span class="choice-icon"><i class="fa fa-random"></i></span><span class="choice-copy"><strong>بین مریض</strong></span></label></div>
                                </div>

                                @if ($fetchData['payment_link_enabled'] ?? false)
                                    <label class="form-label">وضعیت پرداخت</label>
                                    @error('form.registerWithoutPayment')<div class="alert alert-danger py-2">قالب پیامک ارسال لینک پرداخت تعریف نشده است.</div>@enderror
                                    <div class="option-grid two">
                                        <div><input class="choice-input" type="radio" wire:model="form.registerWithoutPayment" value="true" name="payment_status" id="payment_link"><label class="choice-card" for="payment_link"><span class="choice-icon"><i class="fa fa-credit-card"></i></span><span class="choice-copy"><strong>ارسال لینک پرداخت</strong></span></label></div>
                                        <div><input class="choice-input" type="radio" wire:model="form.registerWithoutPayment" value="false" name="payment_status" id="payment_skip"><label class="choice-card" for="payment_skip"><span class="choice-icon"><i class="fa fa-check"></i></span><span class="choice-copy"><strong>ثبت بدون پرداخت</strong></span></label></div>
                                    </div>
                                @else
                                    <label class="form-label">ارسال پیامک</label>
                                    <div class="option-grid two">
                                        <div><input class="choice-input" type="radio" wire:model="form.smsType" value="send" name="sms_status" id="sms_send"><label class="choice-card" for="sms_send"><span class="choice-icon"><i class="fa fa-comment"></i></span><span class="choice-copy"><strong>ارسال شود</strong></span></label></div>
                                        <div><input class="choice-input" type="radio" wire:model="form.smsType" value="unsend" name="sms_status" id="sms_skip"><label class="choice-card" for="sms_skip"><span class="choice-icon"><i class="fa fa-ban"></i></span><span class="choice-copy"><strong>ارسال نشود</strong></span></label></div>
                                    </div>
                                @endif
                                <label for="appointment_description" class="form-label mt-3">توضیحات <span class="field-hint">(اختیاری)</span></label>
                                <textarea class="form-control" id="appointment_description" rows="2" wire:model="form.description"></textarea>
                            </div>
                        </div>
                    @elseif ($step === 3)
                        <div class="appointment-card">
                            <div class="section-title"><span class="section-icon"><i class="fa fa-headphones"></i></span>انتخاب اپراتور</div>
                            <p class="field-hint mb-3">اپراتوری را که این نوبت را پاسخ می‌دهد انتخاب کنید.</p>
                            <select class="form-select @error('form.operator') is-invalid @enderror" wire:model="form.operator">
                                <option value="">بدون اپراتور</option>
                                @foreach ($fetchData['operators'] as $userId => $userName)<option value="{{ $userId }}">{{ $userName }}</option>@endforeach
                            </select>
                            @error('form.operator')<span class="error-message">{{ $message }}</span>@enderror
                        </div>
                    @elseif ($step === 4)
                        <div class="appointment-card text-center py-4">
                            <span class="lookup-icon text-warning"><i class="fa fa-exclamation"></i></span>
                            <h6 class="mb-2">این بازه زمانی قبلاً رزرو شده است</h6>
                            <p class="field-hint mb-0">آیا مطمئن هستید که نوبت دیگری در همین ساعت ثبت شود؟</p>
                        </div>
                    @endif
                </div>

                <div class="modal-footer d-flex align-items-center justify-content-between">
                    <div>
                        @if (in_array($step, [2, 3], true))
                            <button type="button" class="btn btn-light secondary-action" wire:click="privousStep" wire:loading.attr="disabled"><i class="fa fa-arrow-right ms-1"></i>بازگشت</button>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light secondary-action" wire:click="dismisModal" data-bs-dismiss="modal">انصراف</button>
                        <button type="button" class="btn btn-primary primary-action" wire:click="numberSet"
                            wire:loading.attr="disabled" wire:target="numberSet"
                            @disabled($step === 2 && count($fetchData['appointment_kinds'] ?? []) === 0)>
                            <span wire:loading.remove wire:target="numberSet">@if ($step === 1) ادامه <i class="fa fa-arrow-left me-1"></i> @elseif ($step === 4) بله، ثبت شود @else ثبت نهایی <i class="fa fa-check me-1"></i> @endif</span>
                            <span wire:loading wire:target="numberSet"><i class="fa fa-spinner fa-spin ms-1"></i>در حال انجام...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
