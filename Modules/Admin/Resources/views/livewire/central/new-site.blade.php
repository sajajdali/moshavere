<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $isEdit ? 'ویرایش سایت' : 'افزودن سایت جدید' }}</h1>
            <p class="text-muted mb-0">{{ $isEdit ? 'اطلاعات، پشتیبانی و ماژول‌های سایت را ویرایش کنید.' : 'با ثبت فرم، دیتابیس اختصاصی ساخته و تمام migrationها و اطلاعات اولیه اجرا می‌شوند.' }}</p>
        </div>
        <div class="ms-auto pageheader-btn">
            <a href="{{ route('central.dashboard') }}" class="btn btn-secondary">بازگشت به سایت‌ها</a>
        </div>
    </div>

    @error('provisioning')
        <div class="alert alert-danger" role="alert">{{ $message }}</div>
    @enderror

    <div class="card box-shadow-0">
        <div class="card-header border-bottom"><h3 class="card-title">مشخصات سایت</h3></div>
        <div class="card-body">
            <form wire:submit="createOrUpdate" class="form-horizontal">
                <div class="row mb-3">
                    <label for="name" class="col-md-3 form-label">نام سایت</label>
                    <div class="col-md-9">
                        <input class="form-control @error('form.name') is-invalid @enderror" id="name" wire:model="form.name" placeholder="مثلاً کلینیک سلامت" type="text">
                        @error('form.name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="customer" class="col-md-3 form-label">مشتری</label>
                    <div class="col-md-9">
                        <div class="input-group">
                            <select class="form-select @error('form.customer_id') is-invalid @enderror" id="customer" wire:model="form.customer_id">
                                <option value="">انتخاب مشتری...</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->full_name }} — {{ $customer->center_name }} — {{ $customer->mobile }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-outline-primary" type="button" wire:click="openCustomerModal">
                                <i class="fa fa-user-plus me-1"></i> مشتری جدید
                            </button>
                        </div>
                        @error('form.customer_id') <span class="text-danger d-block">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="domain" class="col-md-3 form-label">دامنه</label>
                    <div class="col-md-9">
                        <input class="form-control @error('form.domain') is-invalid @enderror" id="domain" dir="ltr" wire:model="form.domain" placeholder="clinic.example.com" type="text">
                        <small class="text-muted">بدون http، مسیر یا اسلش پایانی وارد شود.</small>
                        @error('form.domain') <span class="text-danger d-block">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="site-id" class="col-md-3 form-label">شناسه سایت</label>
                    <div class="col-md-9">
                        <input class="form-control @error('form.id') is-invalid @enderror" id="site-id" dir="ltr" wire:model="form.id" placeholder="clinic1" type="text" @disabled($isEdit)>
                        <small class="text-muted">فقط حروف کوچک انگلیسی، عدد و زیرخط؛ نام دیتابیس با پیشوند appointmentv4_ ساخته می‌شود.</small>
                        @error('form.id') <span class="text-danger d-block">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="description" class="col-md-3 form-label">توضیحات</label>
                    <div class="col-md-9">
                        <textarea wire:model="form.description" class="form-control @error('form.description') is-invalid @enderror" rows="4" id="description" placeholder="توضیحات تکمیلی"></textarea>
                        @error('form.description') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="support-start" class="col-md-3 form-label">شروع پشتیبانی</label>
                    <div class="col-md-4">
                        <input id="support-start" type="text" dir="ltr" autocomplete="off" data-jdp data-name="form.support_started_at" placeholder="۱۴۰۳/۰۱/۰۱" class="form-control @error('form.support_started_at') is-invalid @enderror" wire:model="form.support_started_at">
                        @error('form.support_started_at') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="support-end" class="col-md-3 form-label">پایان پشتیبانی</label>
                    <div class="col-md-4">
                        <input id="support-end" type="text" dir="ltr" autocomplete="off" data-jdp data-name="form.expires_at" placeholder="۱۴۰۴/۰۱/۰۱" class="form-control @error('form.expires_at') is-invalid @enderror" wire:model="form.expires_at">
                        @error('form.expires_at') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="support-renew-cost" class="col-md-3 form-label">هزینه تمدید پشتیبانی</label>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input id="support-renew-cost" type="text" inputmode="numeric" dir="ltr" wire:model="form.support_renew_cost" class="form-control @error('form.support_renew_cost') is-invalid @enderror" placeholder="{{ number_format($this->defaultRenewalCosts['support']) }}">
                            <span class="input-group-text">تومان</span>
                        </div>
                        <small class="text-muted">خالی باشد، مبلغ عمومی {{ number_format($this->defaultRenewalCosts['support']) }} تومان اعمال می‌شود.</small>
                        @error('form.support_renew_cost') <span class="text-danger d-block">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="server-renew-cost" class="col-md-3 form-label">هزینه تمدید سرور</label>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input id="server-renew-cost" type="text" inputmode="numeric" dir="ltr" wire:model="form.server_renew_cost" class="form-control @error('form.server_renew_cost') is-invalid @enderror" placeholder="{{ number_format($this->defaultRenewalCosts['server']) }}">
                            <span class="input-group-text">تومان</span>
                        </div>
                        <small class="text-muted">خالی باشد، مبلغ عمومی {{ number_format($this->defaultRenewalCosts['server']) }} تومان اعمال می‌شود.</small>
                        @error('form.server_renew_cost') <span class="text-danger d-block">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-4">
                    <label class="col-md-3 form-label">ماژول‌های فعال</label>
                    <div class="col-md-9">
                        <div class="module-options-grid">
                            @foreach ($moduleOptions as $moduleName => $moduleLabel)
                                <div class="module-option" wire:key="module-{{ $moduleName }}">
                                    <input class="module-option-input" type="checkbox" wire:model="selectedModules"
                                           value="{{ $moduleName }}" id="module-{{ $moduleName }}">
                                    <label class="module-option-card" for="module-{{ $moduleName }}">
                                        <span class="module-option-check"><i class="fa fa-check"></i></span>
                                        <span class="module-option-copy">
                                            <strong>{{ $moduleLabel }}</strong>
                                            <small dir="ltr">{{ $moduleName }}</small>
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted d-block mt-2">ماژول‌های هسته‌ای مدیریت، کاربران، تنظیمات و نمای سایت همیشه فعال‌اند.</small>
                        @error('selectedModules') <span class="text-danger d-block">{{ $message }}</span> @enderror
                        @error('selectedModules.*') <span class="text-danger d-block">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-9 offset-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" wire:model.live="form.disabled" id="disabled">
                            <label class="form-check-label" for="disabled">سایت فعلاً غیرفعال باشد</label>
                        </div>
                        @if ($form['disabled'])
                            <div class="mt-3">
                                <label for="disabled-message" class="form-label fw-semibold">پیامی که مدیر و کاربران سایت می‌بینند</label>
                                <textarea id="disabled-message" wire:model="form.disabled_message" rows="4" class="form-control @error('form.disabled_message') is-invalid @enderror" placeholder="مثلاً: این سایت موقتاً غیرفعال شده است. لطفاً برای فعال‌سازی با واحد پشتیبانی تماس بگیرید."></textarea>
                                <small class="text-muted">این پیام هنگام ورود به تمام بخش‌های سایت نمایش داده می‌شود.</small>
                                @error('form.disabled_message') <span class="text-danger d-block">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('central.dashboard') }}" class="btn btn-secondary">انصراف</a>
                    <button type="submit" class="btn btn-success" wire:loading.attr="disabled" wire:target="createOrUpdate">
                        <span wire:loading.remove wire:target="createOrUpdate">{{ $isEdit ? 'ذخیره تغییرات' : 'ساخت سایت و دیتابیس' }}</span>
                        <span wire:loading wire:target="createOrUpdate">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                            در حال ساخت دیتابیس و آماده‌سازی سایت...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @include('admin::livewire.central.partials.customer-modal')
</div>

@push('styles')
<style>
    .module-options-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }
    .module-option { min-width: 0; }
    .module-option-input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }
    .module-option-card {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 72px;
        margin: 0;
        padding: 14px 16px;
        cursor: pointer;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        transition: border-color .18s ease, box-shadow .18s ease, background-color .18s ease;
    }
    .module-option-card:hover {
        border-color: #94a3b8;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .07);
    }
    .module-option-check {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 24px;
        width: 24px;
        height: 24px;
        color: transparent;
        border: 2px solid #cbd5e1;
        border-radius: 7px;
        background: #fff;
        font-size: 12px;
        transition: all .18s ease;
    }
    .module-option-copy { min-width: 0; line-height: 1.5; }
    .module-option-copy strong { display: block; color: #334155; font-size: 14px; }
    .module-option-copy small { display: block; color: #94a3b8; font-size: 11px; }
    .module-option-input:checked + .module-option-card {
        border-color: #3b82f6;
        background: #eff6ff;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, .10);
    }
    .module-option-input:checked + .module-option-card .module-option-check {
        color: #fff;
        border-color: #3b82f6;
        background: #3b82f6;
    }
    .module-option-input:focus-visible + .module-option-card {
        outline: 3px solid rgba(59, 130, 246, .25);
        outline-offset: 2px;
    }
    @media (max-width: 1199.98px) {
        .module-options-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 575.98px) {
        .module-options-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@script
<script>
    jalaliDatepicker.startWatch({
        zIndex: 99999,
        persianDigits: false,
        autoHide: true,
        hideAfterChange: true
    });
</script>
@endscript
