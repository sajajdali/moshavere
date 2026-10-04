<div class="sar-root" dir="rtl">
    @php
        $kinds = $fetchData['appointment_kinds'] ?? [];
        $hasOperators = isset($fetchData['operators']);
    @endphp

    <div wire:ignore.self class="modal fade" id="RegistrAnAppointment" data-bs-backdrop="static"
        data-bs-keyboard="false" tabindex="-1" aria-labelledby="registrationModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="sar-head">
                    <div class="sar-head-top">
                        <div class="sar-heading">
                            <span class="sar-title" id="registrationModalTitle">ثبت نوبت</span>
                            <span class="sar-when">
                                @if ($appDate)
                                    {{ $appDate }}
                                    @if (!empty($form['time']['from']))
                                        — ساعت {{ substr($form['time']['from'], 0, 5) }} تا {{ substr($form['time']['until'] ?? '', 0, 5) }}
                                    @endif
                                @else
                                    اطلاعات بیمار و نوبت را تکمیل کنید
                                @endif
                            </span>
                        </div>
                        <button type="button" class="sar-close" wire:click="dismisModal" data-bs-dismiss="modal" aria-label="بستن">×</button>
                    </div>
                    <div class="sar-steps" aria-label="مراحل ثبت نوبت">
                        <span class="sar-step-dot active">۱</span><span class="sar-step-label {{ $step === 1 ? 'active' : '' }}">شماره همراه بیمار</span>
                        <span class="sar-step-line"></span>
                        <span class="sar-step-dot {{ $step > 1 ? 'active' : '' }}">۲</span><span class="sar-step-label {{ $step === 2 || $step === 4 ? 'active' : '' }}">اطلاعات نوبت</span>
                        @if ($hasOperators)
                            <span class="sar-step-line"></span>
                            <span class="sar-step-dot {{ $step === 3 ? 'active' : '' }}">۳</span><span class="sar-step-label {{ $step === 3 ? 'active' : '' }}">اپراتور</span>
                        @endif
                    </div>
                </div>

                <div class="sar-body">
                    @error('userNotExists')
                        <div class="sar-notice sar-danger" role="alert"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> {{ $message }}</div>
                    @enderror

                    @if ($step === 1)
                        <label class="sar-field" for="appointment_mobile" x-data>
                            <span class="sar-label">ثبت نوبت با شماره همراه</span>
                            <input type="tel" inputmode="numeric" autocomplete="tel" autofocus maxlength="11"
                                class="sar-input @error('form.number') is-invalid @enderror" id="appointment_mobile" dir="ltr"
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
                            @error('form.number')<span class="sar-error">{{ $message }}</span>@enderror
                        </label>

                        @if ($fetchData['document_number_enabled'] ?? false)
                            <div class="sar-divider"></div>
                            <label class="sar-field" for="appointment_document">
                                <span class="sar-label">یا ثبت نوبت با شماره پرونده</span>
                                <input type="text" inputmode="numeric" autocomplete="off" class="sar-input @error('form.document_number') is-invalid @enderror"
                                    id="appointment_document" wire:model="form.document_number" placeholder="شماره پرونده">
                                @error('form.document_number')<span class="sar-error">{{ $message }}</span>@enderror
                            </label>
                        @endif
                    @elseif ($step === 2)
                        @php
                            // the user-details section stays closed only for a patient who already has a full name;
                            // on a missing or invalid name it opens and turns red
                            $userInfoHasError = $errors->has('form.first_name') || $errors->has('form.last_name');
                            $userHasName = isset($fetchData['user']) && filled($form['first_name'] ?? null) && filled($form['last_name'] ?? null);
                            $userInfoOpen = ! $userHasName || $userInfoHasError;
                        @endphp
                        @if (count($kinds) === 0)
                            <div class="sar-notice sar-danger" role="alert">برای این برنامه هیچ نوع نوبتی فعال نشده است. ابتدا تنظیمات نوبت را اصلاح کنید.</div>
                        @endif

                        <section class="sar-section">
                            <button type="button" class="sar-section-title w-100 {{ $userInfoHasError ? 'sar-has-error' : 'border-0 bg-transparent p-0' }} {{ $userInfoOpen ? '' : 'collapsed' }}"
                                data-bs-toggle="collapse" data-bs-target="#appointment-user-details"
                                aria-expanded="{{ $userInfoOpen ? 'true' : 'false' }}" aria-controls="appointment-user-details">
                                <i class="fa fa-angle-down sar-collapse-icon" aria-hidden="true"></i>
                                <strong>مشخصات کاربری</strong>
                                @if ($userInfoHasError)
                                    <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                                @endif
                                <span></span>
                            </button>
                            <div id="appointment-user-details" class="collapse {{ $userInfoOpen ? 'show' : '' }}">
                                <div class="sar-grid">
                                    <label class="sar-field" for="appointment-first-name"><span class="sar-label">نام کاربر</span><input type="text" class="sar-input @error('form.first_name') is-invalid @enderror" id="appointment-first-name" wire:model="form.first_name">@error('form.first_name')<span class="sar-error">{{ $message }}</span>@enderror</label>
                                    <label class="sar-field" for="appointment-last-name"><span class="sar-label">نام خانوادگی</span><input type="text" class="sar-input @error('form.last_name') is-invalid @enderror" id="appointment-last-name" wire:model="form.last_name">@error('form.last_name')<span class="sar-error">{{ $message }}</span>@enderror</label>
                                    <label class="sar-field sar-span-2" for="appointment-document-registration"><span class="sar-label">شماره پرونده</span><input type="text" inputmode="numeric" class="sar-input" id="appointment-document-registration" wire:model="form.document_number"></label>
                                </div>
                            </div>
                        </section>

                        <section class="sar-section">
                            <div class="sar-section-title"><strong>زمان نوبت</strong><span></span></div>
                            <div class="sar-grid">
                                <label class="sar-field" for="appointment-from"><span class="sar-label">از ساعت</span><input type="time" class="sar-input @error('form.time.from') is-invalid @enderror" id="appointment-from" wire:model="form.time.from">@error('form.time.from')<span class="sar-error">{{ $message }}</span>@enderror</label>
                                <label class="sar-field" for="appointment-until"><span class="sar-label">تا ساعت</span><input type="time" class="sar-input @error('form.time.until') is-invalid @enderror" id="appointment-until" wire:model="form.time.until">@error('form.time.until')<span class="sar-error">{{ $message }}</span>@enderror</label>
                            </div>
                        </section>

                        <section class="sar-section">
                            <div class="sar-section-title"><strong>نوع نوبت</strong><span></span></div>
                            <div class="sar-choices">
                                <span class="sar-choice"><input type="radio" wire:model="form.appType" value="main_app" name="appTypeRadio" id="appointment-main"><label for="appointment-main">نوبت اصلی</label></span>
                                <span class="sar-choice"><input type="radio" wire:model="form.appType" value="in_between" name="appTypeRadio" id="appointment-between"><label for="appointment-between">بین مریض</label></span>
                            </div>
                        </section>

                        @if (count($kinds) > 1)
                            <section class="sar-section">
                                <div class="sar-section-title"><strong>نحوه برگزاری نوبت</strong><span></span></div>
                                @error('form.kind')<span class="sar-error">{{ $message }}</span>@enderror
                                <div class="sar-choices">
                                    @foreach ($kinds as $kind)
                                        <span class="sar-choice"><input type="radio" wire:model.live="form.kind" value="{{ $kind['value'] }}" name="appointmentKind" id="appointment-kind-{{ $kind['value'] }}"><label for="appointment-kind-{{ $kind['value'] }}">نوبت {{ $kind['name'] }}</label></span>
                                    @endforeach
                                </div>
                            </section>
                        @elseif (count($kinds) === 1)
                            <section class="sar-section">
                                <div class="sar-section-title"><strong>نحوه برگزاری نوبت</strong><span></span></div>
                                <div class="sar-choices"><span class="sar-choice"><input type="radio" checked disabled id="appointment-kind-single"><label for="appointment-kind-single">نوبت {{ $kinds[0]['name'] }}</label></span></div>
                            </section>
                        @endif

                        @if ($this->paymentEnabledForSelectedKind())
                            <section class="sar-section">
                                <div class="sar-section-title"><strong>وضعیت پرداخت</strong><span></span></div>
                                @error('form.payment_registration')<div class="sar-notice sar-danger">{{ $message }}</div>@enderror
                                <div class="sar-choices">
                                    <span class="sar-choice"><input type="radio" wire:model="form.payment_registration" value="confirmed" name="payment_registration" id="payment_confirmed"><label for="payment_confirmed">ثبت تأییدشده، بدون نیاز به پرداخت</label></span>
                                    <span class="sar-choice"><input type="radio" wire:model="form.payment_registration" value="payment_link" name="payment_registration" id="payment_link"><label for="payment_link">لینک پرداخت ارسال شود</label></span>
                                </div>
                            </section>
                        @endif

                        <section class="sar-section">
                            <div class="sar-section-title"><strong>ارسال پیامک</strong><span></span></div>
                            <div class="sar-choices">
                                <span class="sar-choice"><input type="radio" wire:model="form.smsType" value="send" name="smsStatusType" id="appointment-sms"><label for="appointment-sms">پیامک ارسال شود</label></span>
                                <span class="sar-choice"><input type="radio" wire:model="form.smsType" value="unsend" name="smsStatusType" id="appointment-no-sms"><label for="appointment-no-sms">پیامک ارسال نشود</label></span>
                            </div>
                        </section>

                        <section class="sar-section">
                            <div class="sar-section-title"><strong>توضیحات</strong><span></span></div>
                            <label class="sar-field" for="appointment-description"><span class="sar-label">توضیحات نوبت</span><textarea class="sar-textarea" id="appointment-description" rows="3" wire:model="form.description" placeholder="مثلاً: معرفی‌شده از بخش داخلی"></textarea></label>
                        </section>
                    @elseif ($step === 3)
                        <section class="sar-section">
                            <div class="sar-section-title"><strong>اپراتور نوبت</strong><span></span></div>
                            <label class="sar-field" for="appointment-operator">
                                <span class="sar-label">انتخاب اپراتور</span>
                                <select class="sar-select @error('form.operator') is-invalid @enderror" id="appointment-operator" wire:model="form.operator">
                                    <option value="">بدون اپراتور</option>
                                    @foreach ($fetchData['operators'] ?? [] as $user_id => $user_name)<option value="{{ $user_id }}">{{ $user_name }}</option>@endforeach
                                </select>
                                @error('form.operator')<span class="sar-error">{{ $message }}</span>@enderror
                            </label>
                        </section>
                    @elseif ($step === 4)
                        <div class="sar-notice"><strong>تداخل زمانی</strong><br>در ساعت انتخابی شما یک نوبت ثبت شده است. آیا مایل هستید این نوبت نیز ثبت شود؟</div>
                    @endif
                </div>

                <div class="sar-foot">
                    @if (in_array($step, [2, 3], true))
                        <button type="button" class="sar-button sar-secondary sar-back" wire:click="privousStep" wire:loading.attr="disabled" wire:target="privousStep"><i class="fa fa-angle-right" aria-hidden="true"></i><span>مرحله قبل</span></button>
                    @endif
                    <div class="sar-foot-actions">
                        <button type="button" class="sar-button sar-primary" wire:click="numberSet" wire:loading.attr="disabled" wire:target="numberSet"
                            @disabled($step === 2 && count($kinds) === 0)>
                            <span wire:loading.remove wire:target="numberSet">@if ($step === 1) ادامه @elseif($step === 4) بله، ثبت شود @else ثبت نوبت @endif</span>
                            <span class="spinner-border spinner-border-sm sar-loading" wire:loading wire:target="numberSet" role="status" aria-hidden="true"></span>
                        </button>
                        <button type="button" class="sar-button sar-secondary" wire:click="dismisModal" data-bs-dismiss="modal">بی‌خیال</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
