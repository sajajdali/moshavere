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
                                    اطلاعات مراجع و نوبت را تکمیل کنید
                                @endif
                            </span>
                        </div>
                        <button type="button" class="sar-close" wire:click="dismisModal" data-bs-dismiss="modal" aria-label="بستن">×</button>
                    </div>
                    <div class="sar-steps" aria-label="مراحل ثبت نوبت">
                        <span class="sar-step-dot active">۱</span><span class="sar-step-label {{ $step === 1 ? 'active' : '' }}">شماره همراه مراجع</span>
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
                        <div class="sar-field" style="position:relative">
                            <label class="sar-label" for="appointment_patient_search">جستجوی مراجع با نام یا نام خانوادگی</label>
                            <input type="text" autocomplete="off" class="sar-input" id="appointment_patient_search"
                                wire:model.live.debounce.300ms="patientSearch" placeholder="نام یا نام خانوادگی مراجع">
                            @if (count($this->patientResults))
                                <div class="list-group shadow-sm" style="position:absolute;top:100%;inset-inline:0;z-index:20">
                                    @foreach ($this->patientResults as $patient)
                                        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between"
                                            wire:key="patient-{{ $patient['mobile'] }}" wire:click="selectPatient('{{ $patient['mobile'] }}')">
                                            <span>{{ $patient['name'] ?: 'بدون نام' }}</span>
                                            <span dir="ltr" class="text-muted">{{ $patient['mobile'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @elseif (mb_strlen(trim($patientSearch)) >= 2)
                                <span class="sar-error" style="color:inherit;opacity:.7">مراجعی با این نام یافت نشد.</span>
                            @endif
                        </div>
                        <div class="sar-divider"></div>
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
                            $profileFields = ['first_name', 'last_name', 'national_code', 'birth_date', 'father_name', 'marital_status', 'acquaintance'];
                            $userInfoHasError = collect($profileFields)->contains(fn ($field) => $errors->has('form.'.$field));
                            $userHasProfile = isset($fetchData['user']) && collect($profileFields)->every(fn ($field) => filled($form[$field] ?? null));
                            $userInfoOpen = ! $userHasProfile || $userInfoHasError;
                            $birthAge = $this->birthAge();
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
                                    <label class="sar-field" for="appointment-first-name"><span class="sar-label">نام کاربر <b style="color:#d92d20">*</b></span><input type="text" class="sar-input @error('form.first_name') is-invalid @enderror" id="appointment-first-name" wire:model="form.first_name">@error('form.first_name')<span class="sar-error">{{ $message }}</span>@enderror</label>
                                    <label class="sar-field" for="appointment-last-name"><span class="sar-label">نام خانوادگی <b style="color:#d92d20">*</b></span><input type="text" class="sar-input @error('form.last_name') is-invalid @enderror" id="appointment-last-name" wire:model="form.last_name">@error('form.last_name')<span class="sar-error">{{ $message }}</span>@enderror</label>
                                    <label class="sar-field" for="appointment-mobile-registration"><span class="sar-label">شماره موبایل</span><input type="text" class="sar-input" id="appointment-mobile-registration" dir="ltr" value="{{ $form['number'] ?? '' }}" disabled></label>
                                    <label class="sar-field" for="appointment-document-registration"><span class="sar-label">شماره پرونده</span><input type="text" inputmode="numeric" class="sar-input" id="appointment-document-registration" wire:model="form.document_number"></label>
                                    <label class="sar-field" for="appointment-father-name"><span class="sar-label">نام پدر <b style="color:#d92d20">*</b></span><input type="text" class="sar-input @error('form.father_name') is-invalid @enderror" id="appointment-father-name" wire:model="form.father_name">@error('form.father_name')<span class="sar-error">{{ $message }}</span>@enderror</label>
                                    <label class="sar-field" for="appointment-national-code"><span class="sar-label">کد ملی <b style="color:#d92d20">*</b></span><input type="text" inputmode="numeric" maxlength="10" autocomplete="off" dir="ltr" class="sar-input @error('form.national_code') is-invalid @enderror" id="appointment-national-code" wire:model="form.national_code">@error('form.national_code')<span class="sar-error">{{ $message }}</span>@enderror</label>
                                    <label class="sar-field" for="appointment-birth-date"><span class="sar-label">تاریخ تولد (شمسی) <b style="color:#d92d20">*</b></span><input type="text" data-jdp data-jdp-only-date data-jdp-min-date="1300/01/01" data-jdp-max-date="today" inputmode="numeric" maxlength="10" autocomplete="off" dir="ltr" placeholder="1370/05/21" class="sar-input @error('form.birth_date') is-invalid @enderror" id="appointment-birth-date" wire:model.live.debounce.500ms="form.birth_date">@error('form.birth_date')<span class="sar-error">{{ $message }}</span>@enderror</label>
                                    <label class="sar-field" for="appointment-age"><span class="sar-label">سن</span><input type="text" class="sar-input" id="appointment-age" value="{{ $birthAge !== null ? $birthAge.' سال' : '' }}" placeholder="از روی تاریخ تولد محاسبه می‌شود" disabled></label>
                                    <label class="sar-field" for="appointment-marital-status"><span class="sar-label">وضعیت تأهل <b style="color:#d92d20">*</b></span><select class="sar-select @error('form.marital_status') is-invalid @enderror" id="appointment-marital-status" wire:model="form.marital_status"><option value="">انتخاب کنید...</option>@foreach (\Modules\AppointmentUser\Livewire\Admin\AddAppointment\Modal\SpecificDayAppointmentRegistrationModal::MARITAL_STATUSES as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>@error('form.marital_status')<span class="sar-error">{{ $message }}</span>@enderror</label>
                                    <label class="sar-field" for="appointment-acquaintance"><span class="sar-label">نحوه آشنایی با مرکز <b style="color:#d92d20">*</b></span><select class="sar-select @error('form.acquaintance') is-invalid @enderror" id="appointment-acquaintance" wire:model="form.acquaintance"><option value="">انتخاب کنید...</option>@foreach (\Modules\AppointmentUser\Livewire\Admin\AddAppointment\Modal\SpecificDayAppointmentRegistrationModal::ACQUAINTANCE_OPTIONS as $label)<option value="{{ $label }}">{{ $label }}</option>@endforeach</select>@error('form.acquaintance')<span class="sar-error">{{ $message }}</span>@enderror</label>
                                </div>
                            </div>
                        </section>

                        <section class="sar-section">
                            <div class="sar-section-title"><strong>زمان نوبت</strong><span></span></div>
                            <div class="sar-grid">
                                {{-- 24-hour time fields: a native type="time" input shows AM/PM on 12-hour locales.
                                     Type HH:MM (auto-formatted) or tap an hour / minute in the popup. --}}
                                <style>
                                    .sar-tp{position:relative}
                                    .sar-tp-input{text-align:center;letter-spacing:2px;font-variant-numeric:tabular-nums;direction:ltr}
                                    .sar-tp-pop{position:absolute;top:calc(100% + 6px);inset-inline:0;z-index:30;padding:12px;border:1px solid #e0e3e8;border-radius:12px;background:#fff;box-shadow:0 10px 30px rgba(20,23,28,.14);direction:ltr}
                                    .sar-tp-cap{margin:0 0 6px;font-size:11.5px;color:#6b7280;direction:rtl;text-align:right}
                                    .sar-tp-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:4px;margin-bottom:10px}
                                    .sar-tp-grid:last-child{margin-bottom:0}
                                    .sar-tp-cell{padding:7px 0;border:1px solid transparent;border-radius:8px;background:#f4f5f7;font-size:13px;color:#14171c;cursor:pointer;font-variant-numeric:tabular-nums}
                                    .sar-tp-cell:hover{background:#e6f4f2}
                                    .sar-tp-cell.on{background:#0d9488;color:#fff}
                                </style>
                                @foreach (['from' => ['appointment-from', 'از ساعت'], 'until' => ['appointment-until', 'تا ساعت']] as $timeKey => [$timeId, $timeLabel])
                                    <div class="sar-field sar-tp"
                                        x-data="{
                                            t: $wire.entangle('form.time.{{ $timeKey }}'),
                                            text: '',
                                            open: false,
                                            init() {
                                                this.text = (this.t || '').slice(0, 5);
                                                this.$watch('t', v => { if ((v || '').slice(0, 5) !== this.text) this.text = (v || '').slice(0, 5); });
                                            },
                                            get h() { return this.text.slice(0, 2) },
                                            get m() { return this.text.slice(3, 5) },
                                            format(raw) {
                                                const fa = '۰۱۲۳۴۵۶۷۸۹', ar = '٠١٢٣٤٥٦٧٨٩';
                                                let d = raw.replace(/[۰-۹٠-٩]/g, c => { const i = fa.indexOf(c); return i > -1 ? i : ar.indexOf(c); }).replace(/\D/g, '').slice(0, 4);
                                                if (d.length === 1 && d > '2') d = '0' + d;
                                                if (d.length >= 2 && +d.slice(0, 2) > 23) d = '23' + d.slice(2);
                                                if (d.length >= 4 && +d.slice(2) > 59) d = d.slice(0, 2) + '59';
                                                return d.length > 2 ? d.slice(0, 2) + ':' + d.slice(2) : d;
                                            },
                                            typed(e) {
                                                this.text = this.format(e.target.value);
                                                e.target.value = this.text;
                                                this.t = this.text.length === 5 ? this.text : '';
                                            },
                                            finish() {
                                                if (this.text.length === 1) this.text = '0' + this.text;
                                                if (this.text.length === 2) this.text += ':00';
                                                else if (this.text.length === 4) this.text += '0';
                                                this.t = this.text.length === 5 ? this.text : '';
                                            },
                                            setHour(v) { this.text = v + ':' + (this.m.length === 2 ? this.m : '00'); this.t = this.text; },
                                            setMinute(v) { this.text = (this.h.length === 2 ? this.h : '00') + ':' + v; this.t = this.text; this.open = false; }
                                        }"
                                        @click.outside="open = false" @keydown.escape="open = false">
                                        <label class="sar-label" for="{{ $timeId }}">{{ $timeLabel }}</label>
                                        <input type="text" id="{{ $timeId }}" inputmode="numeric" autocomplete="off" maxlength="5" placeholder="--:--"
                                            class="sar-input sar-tp-input @error('form.time.' . $timeKey) is-invalid @enderror"
                                            :value="text" @input="typed($event)" @focus="open = true" @blur="finish()" @keydown.enter="open = false">
                                        <div class="sar-tp-pop" x-show="open" x-cloak x-transition.opacity.duration.100ms>
                                            <p class="sar-tp-cap">ساعت</p>
                                            <div class="sar-tp-grid">
                                                @for ($hour = 0; $hour < 24; $hour++)
                                                    <button type="button" class="sar-tp-cell" :class="{ on: h === '{{ sprintf('%02d', $hour) }}' }"
                                                        @mousedown.prevent @click="setHour('{{ sprintf('%02d', $hour) }}')">{{ sprintf('%02d', $hour) }}</button>
                                                @endfor
                                            </div>
                                            <p class="sar-tp-cap">دقیقه</p>
                                            <div class="sar-tp-grid">
                                                @foreach (range(0, 55, 5) as $minute)
                                                    <button type="button" class="sar-tp-cell" :class="{ on: m === '{{ sprintf('%02d', $minute) }}' }"
                                                        @mousedown.prevent @click="setMinute('{{ sprintf('%02d', $minute) }}')">{{ sprintf('%02d', $minute) }}</button>
                                                @endforeach
                                            </div>
                                        </div>
                                        @error('form.time.' . $timeKey)<span class="sar-error">{{ $message }}</span>@enderror
                                    </div>
                                @endforeach
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
