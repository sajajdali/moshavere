@php
    use Modules\User\Support\PatientProfile;

    $existsAndLocked = $existing && ! $existing['can_edit'];
    // the rest of the form unlocks once a complete mobile number has been checked
    $locked = ! $mobileChecked || $existsAndLocked;
    $birthAge = $this->birthAge();
    $star = '<b class="ap-star">*</b>';
@endphp
<div class="ap-page">
    <style>
        .ap-page{--ap-primary:#0f766e;--ap-primary-soft:#e6f4f2;--ap-border:#e3e7ee;--ap-muted:#667085;--ap-text:#101828;--ap-danger:#d92d20;--ap-warn:#b54708;--ap-info:#175cd3;max-width:980px;margin:0 auto}
        .ap-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:18px}
        .ap-title{display:flex;align-items:center;gap:12px;margin:0;font-size:22px;font-weight:800;color:var(--ap-text)}
        .ap-title-icon{display:grid;place-items:center;width:44px;height:44px;border-radius:14px;background:var(--ap-primary-soft);color:var(--ap-primary);font-size:19px}
        .ap-sub{margin:4px 0 0;color:var(--ap-muted);font-size:13px}
        .ap-card{background:#fff;border:1px solid var(--ap-border);border-radius:18px;box-shadow:0 12px 32px rgba(16,24,40,.06);overflow:hidden}
        .ap-hero{padding:22px 24px;background:linear-gradient(135deg,#f0fbf9 0%,#f7f9ff 100%);border-bottom:1px solid var(--ap-border)}
        .ap-hero-row{display:flex;align-items:flex-end;gap:16px;flex-wrap:wrap}
        .ap-mobile{flex:1 1 320px;min-width:0}
        .ap-mobile .ap-input{height:56px;font-size:21px;letter-spacing:2px;font-weight:700;padding-inline-start:50px}
        .ap-mobile-wrap{position:relative}
        .ap-mobile-icon{position:absolute;inset-inline-start:16px;top:50%;transform:translateY(-50%);color:var(--ap-primary);font-size:18px;pointer-events:none}
        .ap-chip{display:inline-flex;align-items:center;gap:7px;height:34px;padding:0 14px;border-radius:999px;font-size:13px;font-weight:700;border:1px solid transparent;white-space:nowrap}
        .ap-chip-idle{background:#f2f4f7;color:var(--ap-muted);border-color:#e4e7ec}
        .ap-chip-new{background:#ecfdf3;color:#067647;border-color:#abefc6}
        .ap-chip-found{background:#eff8ff;color:var(--ap-info);border-color:#b2ddff}
        .ap-chip-locked{background:#fffaeb;color:var(--ap-warn);border-color:#fedf89}
        .ap-found{display:flex;gap:14px;align-items:flex-start;margin:16px 0 0;padding:14px 16px;border-radius:14px;font-size:13.5px;line-height:1.9}
        .ap-found.is-info{background:#eff8ff;border:1px solid #b2ddff;color:#0b3a85}
        .ap-found.is-warn{background:#fffaeb;border:1px solid #fedf89;color:#7a2e0e}
        .ap-found i.ap-found-icon{font-size:20px;margin-top:3px}
        .ap-found a{font-weight:700;text-decoration:underline}
        .ap-body{padding:6px 24px 8px}
        .ap-section{padding:20px 0;border-bottom:1px dashed var(--ap-border)}
        .ap-section:last-of-type{border-bottom:0}
        .ap-section-title{display:flex;align-items:center;gap:10px;margin:0 0 14px;font-size:15px;font-weight:800;color:var(--ap-text)}
        .ap-section-title i{display:grid;place-items:center;width:30px;height:30px;border-radius:9px;background:var(--ap-primary-soft);color:var(--ap-primary);font-size:13px}
        .ap-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
        .ap-field{display:flex;flex-direction:column;gap:6px;min-width:0}
        .ap-label{font-size:13px;font-weight:700;color:#344054}
        .ap-star{color:var(--ap-danger);margin-inline-start:3px}
        .ap-input,.ap-select{width:100%;height:46px;padding:0 14px;border:1px solid #d0d5dd;border-radius:12px;background:#fff;color:var(--ap-text);font-size:14.5px;transition:border-color .15s,box-shadow .15s,background .15s}
        .ap-input:focus,.ap-select:focus{outline:0;border-color:var(--ap-primary);box-shadow:0 0 0 4px rgba(15,118,110,.14)}
        .ap-input:disabled,.ap-select:disabled{background:#f2f4f7;color:#98a2b3;cursor:not-allowed}
        .ap-input.is-invalid,.ap-select.is-invalid{border-color:var(--ap-danger);background:#fffbfa}
        .ap-input.is-invalid:focus,.ap-select.is-invalid:focus{box-shadow:0 0 0 4px rgba(217,45,32,.12)}
        .ap-input[dir=ltr]{text-align:left}
        .ap-error{color:var(--ap-danger);font-size:12.5px;font-weight:600}
        .ap-hint{color:var(--ap-muted);font-size:12.5px}
        .ap-age{display:flex;align-items:center;gap:8px;height:46px;padding:0 14px;border:1px dashed #cfd6e0;border-radius:12px;background:#fafbfc;color:var(--ap-muted);font-weight:700}
        .ap-age.has-value{border-style:solid;border-color:#abefc6;background:#ecfdf3;color:#067647}
        .ap-roles{display:flex;flex-wrap:wrap;gap:8px}
        .ap-role-badge{display:inline-flex;align-items:center;height:30px;padding:0 12px;border-radius:999px;background:#f2f4f7;color:#344054;font-size:12.5px;font-weight:700}
        .ap-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:16px 24px;background:#fafbfc;border-top:1px solid var(--ap-border)}
        .ap-foot-actions{display:flex;gap:10px;flex-wrap:wrap}
        .ap-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;height:46px;padding:0 22px;border-radius:12px;border:1px solid transparent;font-size:14.5px;font-weight:800;cursor:pointer;transition:transform .12s,box-shadow .12s,background .12s}
        .ap-btn:disabled{opacity:.55;cursor:not-allowed}
        .ap-btn-primary{background:var(--ap-primary);color:#fff;box-shadow:0 8px 18px rgba(15,118,110,.26)}
        .ap-btn-primary:hover:not(:disabled){transform:translateY(-1px);background:#0b625b;color:#fff}
        .ap-btn-ghost{background:#fff;color:#344054;border-color:#d0d5dd}
        .ap-btn-ghost:hover:not(:disabled){background:#f9fafb;color:#344054}
        .ap-spin{display:inline-block;width:15px;height:15px;border:2px solid currentColor;border-top-color:transparent;border-radius:50%;animation:ap-rot .7s linear infinite}
        @keyframes ap-rot{to{transform:rotate(360deg)}}
        @media (max-width:720px){.ap-grid{grid-template-columns:minmax(0,1fr)}.ap-hero,.ap-foot{padding-inline:16px}.ap-body{padding-inline:16px}.ap-foot-actions,.ap-btn{width:100%}}
    </style>

    <div class="ap-head">
        <div>
            <h1 class="ap-title"><span class="ap-title-icon"><i class="fa fa-user-plus" aria-hidden="true"></i></span>افزودن مراجع</h1>
            <p class="ap-sub">ابتدا شماره موبایل را وارد کنید؛ اگر مراجع قبلاً ثبت شده باشد، اطلاعات پرونده‌اش بارگذاری می‌شود.</p>
        </div>
        <a href="{{ route('admin.user.index') }}" class="ap-btn ap-btn-ghost" style="height:40px"><i class="fa fa-arrow-right" aria-hidden="true"></i> بازگشت به لیست کاربران</a>
    </div>

    @include('admin::layouts.components.alert')

    <form class="ap-card" wire:submit.prevent="save(false)" autocomplete="off" novalidate>
        <div class="ap-hero">
            <div class="ap-hero-row">
                <label class="ap-field ap-mobile" for="ap-mobile">
                    <span class="ap-label">شماره موبایل {!! $star !!}</span>
                    <span class="ap-mobile-wrap">
                        <i class="fa fa-mobile-screen-button ap-mobile-icon" aria-hidden="true"></i>
                        <input type="tel" inputmode="numeric" maxlength="11" autofocus dir="ltr" placeholder="09123456789"
                            id="ap-mobile" class="ap-input @error('form.mobile') is-invalid @enderror"
                            wire:model.live.debounce.300ms="form.mobile" x-data
                            x-on:input="$el.value = $el.value.replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d)).replace(/[^0-9]/g, '').slice(0, 11)">
                    </span>
                    @error('form.mobile')<span class="ap-error">{{ $message }}</span>@enderror
                </label>
                <div>
                    <span wire:loading wire:target="form.mobile" class="ap-chip ap-chip-idle"><span class="ap-spin"></span> در حال جستجو...</span>
                    <span wire:loading.remove wire:target="form.mobile">
                        @if ($existsAndLocked)
                            <span class="ap-chip ap-chip-locked"><i class="fa fa-lock"></i> ثبت‌شده؛ بدون دسترسی ویرایش</span>
                        @elseif ($existing)
                            <span class="ap-chip ap-chip-found"><i class="fa fa-user-check"></i> مراجع موجود</span>
                        @elseif ($mobileChecked)
                            <span class="ap-chip ap-chip-new"><i class="fa fa-circle-check"></i> مراجع جدید</span>
                        @else
                            <span class="ap-chip ap-chip-idle"><i class="fa fa-keyboard"></i> {{ strlen($form['mobile'] ?? '') }}/۱۱ رقم</span>
                        @endif
                    </span>
                </div>
            </div>

            @if ($existing)
                <div class="ap-found {{ $existsAndLocked ? 'is-warn' : 'is-info' }}" role="status">
                    <i class="fa {{ $existsAndLocked ? 'fa-triangle-exclamation' : 'fa-circle-info' }} ap-found-icon" aria-hidden="true"></i>
                    <div>
                        @if ($existsAndLocked)
                            این شماره قبلاً در سیستم ثبت شده است و شما اجازه‌ی ویرایش این کاربر را ندارید.
                        @else
                            این شماره قبلاً به نام <strong>{{ $existing['name'] !== '' ? $existing['name'] : 'بدون نام' }}</strong>
                            @if (count($existing['roles']))
                                ({{ implode('، ', $existing['roles']) }})
                            @endif
                            ثبت شده است. اطلاعات پرونده بارگذاری شد؛ می‌توانید موارد ناقص را کامل و ذخیره کنید.
                            <div>
                                <a href="{{ route('admin.user.document', $existing['id']) }}">مشاهده پرونده</a>
                                &nbsp;·&nbsp;
                                <a href="{{ route('admin.user.edit', $existing['id']) }}">ویرایش کاربر</a>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif ($mobileChecked)
                <p class="ap-hint" style="margin:12px 0 0"><i class="fa fa-circle-check" style="color:#067647"></i> کاربری با این شماره پیدا نشد؛ مراجع جدید ساخته می‌شود.</p>
            @endif
        </div>

        <div class="ap-body" @if ($locked) style="opacity:.62" @endif>
            <section class="ap-section">
                <h2 class="ap-section-title"><i class="fa fa-id-card" aria-hidden="true"></i> مشخصات هویتی</h2>
                <div class="ap-grid">
                    <label class="ap-field" for="ap-first-name">
                        <span class="ap-label">نام {!! $star !!}</span>
                        <input type="text" id="ap-first-name" class="ap-input @error('form.first_name') is-invalid @enderror" wire:model="form.first_name" placeholder="نام مراجع" @disabled($locked)>
                        @error('form.first_name')<span class="ap-error">{{ $message }}</span>@enderror
                    </label>
                    <label class="ap-field" for="ap-last-name">
                        <span class="ap-label">نام خانوادگی {!! $star !!}</span>
                        <input type="text" id="ap-last-name" class="ap-input @error('form.last_name') is-invalid @enderror" wire:model="form.last_name" placeholder="نام خانوادگی مراجع" @disabled($locked)>
                        @error('form.last_name')<span class="ap-error">{{ $message }}</span>@enderror
                    </label>
                    <label class="ap-field" for="ap-father-name">
                        <span class="ap-label">نام پدر {!! $star !!}</span>
                        <input type="text" id="ap-father-name" class="ap-input @error('form.father_name') is-invalid @enderror" wire:model="form.father_name" placeholder="نام پدر" @disabled($locked)>
                        @error('form.father_name')<span class="ap-error">{{ $message }}</span>@enderror
                    </label>
                    <label class="ap-field" for="ap-national-code">
                        <span class="ap-label">کد ملی {!! $star !!}</span>
                        <input type="text" inputmode="numeric" maxlength="10" dir="ltr" id="ap-national-code" placeholder="۱۰ رقم"
                            class="ap-input @error('form.national_code') is-invalid @enderror" wire:model="form.national_code" @disabled($locked)
                            x-data x-on:input="$el.value = $el.value.replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d)).replace(/[^0-9]/g, '').slice(0, 10)">
                        @error('form.national_code')<span class="ap-error">{{ $message }}</span>@enderror
                    </label>
                </div>
            </section>

            <section class="ap-section">
                <h2 class="ap-section-title"><i class="fa fa-cake-candles" aria-hidden="true"></i> اطلاعات تکمیلی</h2>
                <div class="ap-grid">
                    <label class="ap-field" for="ap-birth-date">
                        <span class="ap-label">تاریخ تولد (شمسی) {!! $star !!}</span>
                        <input type="text" id="ap-birth-date" data-jdp data-jdp-only-date data-jdp-min-date="1300/01/01" data-jdp-max-date="today"
                            inputmode="numeric" maxlength="10" autocomplete="off" dir="ltr" placeholder="1370/05/21"
                            class="ap-input @error('form.birth_date') is-invalid @enderror" wire:model.live.debounce.500ms="form.birth_date" @disabled($locked)>
                        @error('form.birth_date')<span class="ap-error">{{ $message }}</span>@enderror
                    </label>
                    <div class="ap-field">
                        <span class="ap-label">سن</span>
                        <div class="ap-age {{ $birthAge !== null ? 'has-value' : '' }}">
                            <i class="fa fa-hourglass-half" aria-hidden="true"></i>
                            {{ $birthAge !== null ? $birthAge.' سال' : 'از روی تاریخ تولد محاسبه می‌شود' }}
                        </div>
                    </div>
                    <label class="ap-field" for="ap-marital">
                        <span class="ap-label">وضعیت تأهل {!! $star !!}</span>
                        <select id="ap-marital" class="ap-select @error('form.marital_status') is-invalid @enderror" wire:model="form.marital_status" @disabled($locked)>
                            <option value="">انتخاب کنید...</option>
                            @foreach (PatientProfile::MARITAL_STATUSES as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('form.marital_status')<span class="ap-error">{{ $message }}</span>@enderror
                    </label>
                    <label class="ap-field" for="ap-acquaintance">
                        <span class="ap-label">نحوه آشنایی با مرکز {!! $star !!}</span>
                        <select id="ap-acquaintance" class="ap-select @error('form.acquaintance') is-invalid @enderror" wire:model="form.acquaintance" @disabled($locked)>
                            <option value="">انتخاب کنید...</option>
                            @foreach (PatientProfile::ACQUAINTANCE_OPTIONS as $label)
                                <option value="{{ $label }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('form.acquaintance')<span class="ap-error">{{ $message }}</span>@enderror
                    </label>
                </div>
            </section>

            <section class="ap-section">
                <h2 class="ap-section-title"><i class="fa fa-user-shield" aria-hidden="true"></i> نقش کاربر</h2>
                @if ($existing)
                    <div class="ap-roles">
                        @forelse ($existing['roles'] as $roleName)
                            <span class="ap-role-badge">{{ $roleName }}</span>
                        @empty
                            <span class="ap-hint">این کاربر نقشی ندارد.</span>
                        @endforelse
                    </div>
                    <p class="ap-hint" style="margin:8px 0 0">نقش‌های کاربر موجود از این صفحه تغییر نمی‌کند.</p>
                @else
                    <label class="ap-field" for="ap-role" style="max-width:420px">
                        <select id="ap-role" class="ap-select @error('form.role_id') is-invalid @enderror" wire:model="form.role_id" @disabled($locked || $roles->count() < 2)>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}@if ($role->id === $defaultRoleId) (پیش‌فرض)@endif</option>
                            @endforeach
                        </select>
                        @error('form.role_id')<span class="ap-error">{{ $message }}</span>@enderror
                        @if ($roles->count() < 2)
                            <span class="ap-hint">شما فقط می‌توانید مراجع را با نقش پیش‌فرض ایجاد کنید.</span>
                        @endif
                    </label>
                @endif
            </section>
        </div>

        <div class="ap-foot">
            <span class="ap-hint"><b class="ap-star" style="margin:0">*</b> فیلدهای ستاره‌دار الزامی هستند.</span>
            <div class="ap-foot-actions">
                @unless ($existing)
                    <button type="button" class="ap-btn ap-btn-ghost" wire:click="save(true)" wire:loading.attr="disabled" wire:target="save" @disabled($locked)>
                        <i class="fa fa-plus" aria-hidden="true"></i> ثبت و افزودن مراجع بعدی
                    </button>
                @endunless
                <button type="submit" class="ap-btn ap-btn-primary" wire:loading.attr="disabled" wire:target="save" @disabled($locked)>
                    <span wire:loading wire:target="save" class="ap-spin"></span>
                    <i wire:loading.remove wire:target="save" class="fa fa-check" aria-hidden="true"></i>
                    {{ $existing ? 'ذخیره‌ی پرونده' : 'ثبت مراجع' }}
                </button>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            (function () {
                function initPicker() {
                    if (!window.jalaliDatepicker) { return setTimeout(initPicker, 100); }
                    jalaliDatepicker.startWatch({ zIndex: 99999, autoHide: true, hideAfterChange: true });
                }
                initPicker();
                function bindFocus() {
                    Livewire.on('patient-saved', function () {
                        var mobile = document.getElementById('ap-mobile');
                        if (mobile) { mobile.focus(); }
                    });
                }
                if (window.Livewire) { bindFocus(); } else { document.addEventListener('livewire:init', bindFocus); }
            })();
        </script>
    @endpush
</div>
