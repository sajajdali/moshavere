<?php

namespace Modules\Admin\Livewire\Central;

use App\Models\Tenant;
use App\Models\CentralSetting;
use App\Support\TenantModuleAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\Admin\Support\CustomerCreator;
use Modules\User\Entities\User;
use Throwable;

class NewSiteCreateOrUpdate extends Component
{
    public array $form = [
        'name' => '',
        'customer_id' => '',
        'description' => '',
        'domain' => '',
        'id' => '',
        'support_started_at' => '',
        'expires_at' => '',
        'support_renew_cost' => '',
        'server_renew_cost' => '',
        'disabled_message' => '',
        'disabled' => false,
    ];

    public bool $creating = false;

    public array $moduleOptions = [];

    public array $selectedModules = [];

    public ?string $tenantId = null;

    public bool $isEdit = false;

    public bool $showCustomerModal = false;

    public array $customerForm = [
        'first_name' => '', 'last_name' => '', 'center_name' => '',
        'mobile' => '', 'email' => '', 'password' => '', 'password_confirmation' => '',
    ];

    public function mount(?string $new_site = null): void
    {
        $labels = [
            'Absence' => 'مدیریت عدم حضور',
            'Api' => 'وب‌سرویس API',
            'AppointmentSetting' => 'تنظیمات نوبت‌دهی',
            'AppointmentUser' => 'نوبت‌دهی کاربران',
            'Chat' => 'گفتگو و پیام‌رسانی',
            'Discount' => 'تخفیف‌ها',
            'OnlineConsultation' => 'مشاوره آنلاین صوتی',
            'Place' => 'مدیریت مطب و مکان‌ها',
            'Reminder' => 'یادآوری‌ها',
            'Service' => 'خدمات',
            'Speciality' => 'تخصص‌ها',
            'Transaction' => 'تراکنش‌ها',
        ];

        foreach (\Module::allEnabled() as $module) {
            $name = $module->getName();

            if (in_array($name, TenantModuleAccess::CORE_MODULES, true) || $name === 'MigrateOldData') {
                continue;
            }

            $this->moduleOptions[$name] = $labels[$name] ?? $name;
        }

        $this->selectedModules = array_keys($this->moduleOptions);
        $this->form['support_started_at'] = verta(now())->format('Y/m/d');
        $this->form['expires_at'] = verta(now()->addYear())->format('Y/m/d');

        if ($new_site !== null) {
            $tenant = Tenant::query()->with('domains')->findOrFail($new_site);
            $this->tenantId = $tenant->id;
            $this->isEdit = true;
            $this->form = [
                'name' => (string) $tenant->name,
                'customer_id' => (string) $tenant->customer_id,
                'description' => (string) $tenant->description,
                'domain' => (string) optional($tenant->domains->first())->domain,
                'id' => $tenant->id,
                'support_started_at' => $tenant->support_started_at ? verta($tenant->support_started_at)->format('Y/m/d') : '',
                'expires_at' => $tenant->expires_at ? verta($tenant->expires_at)->format('Y/m/d') : '',
                'support_renew_cost' => $tenant->support_renew_cost === null ? '' : (string) $tenant->support_renew_cost,
                'server_renew_cost' => $tenant->server_renew_cost === null ? '' : (string) $tenant->server_renew_cost,
                'disabled_message' => (string) $tenant->disabled_message,
                'disabled' => (bool) $tenant->disabled,
            ];
            $this->selectedModules = is_array($tenant->enabled_modules)
                ? array_values(array_intersect(array_keys($this->moduleOptions), $tenant->enabled_modules))
                : array_keys($this->moduleOptions);
        }
    }

    protected function rules(): array
    {
        return [
            'form.name' => ['required', 'string', 'max:255'],
            'form.customer_id' => [
                'required', 'integer', Rule::exists('users', 'id'),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $isCustomer = User::query()->whereKey($value)
                        ->whereHas('roles', fn ($query) => $query->where('name', 'مشتری'))
                        ->exists();

                    if (! $isCustomer) {
                        $fail('کاربر انتخاب‌شده نقش مشتری ندارد.');
                    }
                },
            ],
            'form.description' => ['nullable', 'string', 'max:2000'],
            'form.disabled' => ['boolean'],
            'form.disabled_message' => ['nullable', 'required_if:form.disabled,true', 'string', 'max:2000'],
            'form.support_started_at' => ['required', 'regex:/^1[34]\d{2}\/(0[1-9]|1[0-2])\/(0[1-9]|[12]\d|3[01])$/'],
            'form.expires_at' => ['required', 'regex:/^1[34]\d{2}\/(0[1-9]|1[0-2])\/(0[1-9]|[12]\d|3[01])$/'],
            'form.support_renew_cost' => ['nullable', 'integer', 'min:0'],
            'form.server_renew_cost' => ['nullable', 'integer', 'min:0'],
            'selectedModules' => ['array'],
            'selectedModules.*' => ['string', Rule::in(array_keys($this->moduleOptions))],
            'form.id' => ['required', 'string', 'max:50', 'regex:/^[a-z][a-z0-9_]*$/', Rule::unique('tenants', 'id')->ignore($this->tenantId)],
            'form.domain' => [
                'required', 'string', 'max:253',
                'regex:/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/',
                Rule::unique('domains', 'domain')->ignore(
                    $this->isEdit ? Tenant::find($this->tenantId)?->domains()->value('id') : null
                ),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (in_array($value, config('tenancy.central_domains', []), true)) {
                        $fail('دامنه مرکزی را نمی‌توان برای یک سایت استفاده کرد.');
                    }
                },
            ],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.name' => 'نام سایت',
            'form.customer_id' => 'مشتری',
            'form.description' => 'توضیحات',
            'form.domain' => 'دامنه',
            'form.id' => 'شناسه سایت',
            'form.support_started_at' => 'تاریخ شروع پشتیبانی',
            'form.expires_at' => 'تاریخ پایان پشتیبانی',
            'form.support_renew_cost' => 'هزینه تمدید پشتیبانی',
            'form.server_renew_cost' => 'هزینه تمدید سرور',
            'form.disabled_message' => 'پیام غیرفعال‌سازی سایت',
            'customerForm.first_name' => 'نام',
            'customerForm.last_name' => 'نام خانوادگی',
            'customerForm.center_name' => 'اسم مرکز',
            'customerForm.mobile' => 'موبایل',
            'customerForm.email' => 'ایمیل',
            'customerForm.password' => 'کلمه عبور',
            'customerForm.password_confirmation' => 'تکرار کلمه عبور',
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => 'وارد کردن :attribute الزامی است.',
            'email' => 'فرمت :attribute صحیح نیست.',
            'unique' => ':attribute قبلاً ثبت شده است.',
            'min' => ':attribute نباید کمتر از :min کاراکتر باشد.',
            'max' => ':attribute نباید بیشتر از :max کاراکتر باشد.',
            'confirmed' => 'تکرار کلمه عبور با کلمه عبور یکسان نیست.',
            'after_or_equal' => ':attribute باید بعد از تاریخ شروع پشتیبانی یا برابر با آن باشد.',
            'regex' => 'فرمت :attribute باید به صورت ۱۴۰۳/۰۱/۰۱ باشد.',
        ];
    }

    public function createOrUpdate(): void
    {
        $this->form['id'] = strtolower(trim($this->form['id'] ?? ''));
        $this->form['domain'] = strtolower(rtrim(trim($this->form['domain'] ?? ''), '.'));
        $this->form['support_renew_cost'] = $this->normalizeOptionalCost($this->form['support_renew_cost'] ?? null);
        $this->form['server_renew_cost'] = $this->normalizeOptionalCost($this->form['server_renew_cost'] ?? null);
        $validated = $this->validate()['form'];
        try {
            $supportStartedAt = Verta::parse($validated['support_started_at'])->toCarbon()->startOfDay();
            $expiresAt = Verta::parse($validated['expires_at'])->toCarbon()->endOfDay();
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'form.support_started_at' => 'تاریخ شروع یا پایان پشتیبانی معتبر نیست.',
            ]);
        }

        if ($expiresAt->lt($supportStartedAt)) {
            throw ValidationException::withMessages([
                'form.expires_at' => 'تاریخ پایان پشتیبانی باید بعد از تاریخ شروع یا برابر با آن باشد.',
            ]);
        }
        $enabledModules = array_values(array_unique(array_merge(
            TenantModuleAccess::CORE_MODULES,
            $this->selectedModules
        )));

        $this->creating = true;
        $tenant = null;

        try {
            if ($this->isEdit) {
                DB::connection(config('tenancy.database.central_connection'))->transaction(function () use ($validated, $enabledModules, $supportStartedAt, $expiresAt): void {
                    $tenant = Tenant::findOrFail($this->tenantId);
                    $tenant->update([
                        'name' => $validated['name'],
                        'customer_id' => (int) $validated['customer_id'],
                        'description' => $validated['description'] ?: null,
                        'disabled' => (bool) $validated['disabled'],
                        'disabled_message' => $validated['disabled_message'] ?: null,
                        'support_started_at' => $supportStartedAt,
                        'expires_at' => $expiresAt,
                        'support_renew_cost' => $validated['support_renew_cost'] === null ? null : (int) $validated['support_renew_cost'],
                        'server_renew_cost' => $validated['server_renew_cost'] === null ? null : (int) $validated['server_renew_cost'],
                        'enabled_modules' => $enabledModules,
                        'online_consultation_enabled' => in_array('OnlineConsultation', $enabledModules, true),
                    ]);
                    $tenant->domains()->firstOrCreate([], ['domain' => $validated['domain']])
                        ->update(['domain' => $validated['domain']]);
                });

                session()->flash('success', 'اطلاعات سایت با موفقیت ویرایش شد.');
                $this->redirectRoute('central.dashboard', navigate: true);

                return;
            }

            // TenantCreated synchronously creates the database, runs every tenant
            // migration and executes the configured module seeders.
            $tenant = Tenant::create([
                'id' => $validated['id'],
                'name' => $validated['name'],
                'customer_id' => (int) $validated['customer_id'],
                'description' => $validated['description'] ?: null,
                'disabled' => (bool) $validated['disabled'],
                'disabled_message' => $validated['disabled_message'] ?: null,
                'support_started_at' => $supportStartedAt,
                'expires_at' => $expiresAt,
                'support_renew_cost' => $validated['support_renew_cost'] === null ? null : (int) $validated['support_renew_cost'],
                'server_renew_cost' => $validated['server_renew_cost'] === null ? null : (int) $validated['server_renew_cost'],
                'enabled_modules' => $enabledModules,
                'online_consultation_enabled' => in_array('OnlineConsultation', $enabledModules, true),
            ]);
            $tenant->domains()->create(['domain' => $validated['domain']]);
        } catch (Throwable $exception) {
            if ($this->isEdit) {
                report($exception);
                $this->addError('provisioning', 'ویرایش سایت انجام نشد: '.$exception->getMessage());
                $this->creating = false;

                return;
            }

            $tenant ??= Tenant::find($validated['id']);

            if ($tenant?->exists) {
                try {
                    $tenant->delete();
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            report($exception);
            $this->addError('provisioning', 'ساخت سایت یا دیتابیس کامل نشد: '.$exception->getMessage());
            $this->creating = false;

            return;
        }

        session()->flash(
            'success',
            "سایت {$validated['name']} و دیتابیس appointmentv4_{$validated['id']} با موفقیت ساخته شدند."
        );

        $this->redirectRoute('central.dashboard', navigate: true);
    }

    public function getDefaultRenewalCostsProperty(): array
    {
        $settings = CentralSetting::current();

        return [
            'support' => (int) $settings->support_renew_cost,
            'server' => (int) $settings->server_renew_cost,
        ];
    }

    private function normalizeOptionalCost(mixed $value): ?string
    {
        $value = strtr(trim((string) $value), [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        return $value === '' ? null : preg_replace('/[^0-9]/', '', $value);
    }

    public function openCustomerModal(): void
    {
        $this->resetValidation();
        $this->showCustomerModal = true;
    }

    public function closeCustomerModal(): void
    {
        $this->showCustomerModal = false;
    }

    public function createCustomer(): void
    {
        $validated = $this->validate([
            'customerForm.first_name' => ['required', 'string', 'max:100'],
            'customerForm.last_name' => ['required', 'string', 'max:100'],
            'customerForm.center_name' => ['required', 'string', 'max:255'],
            'customerForm.mobile' => ['required', 'string', 'max:20', 'unique:users,mobile'],
            'customerForm.email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'customerForm.password' => ['required', 'string', 'min:8', 'confirmed'],
        ])['customerForm'];

        $customer = CustomerCreator::create($validated);
        $this->form['customer_id'] = $customer->id;
        $this->reset('customerForm', 'showCustomerModal');
        $this->resetValidation();
    }

    #[Layout('admin::layouts.central.app')]
    public function render()
    {
        return view('admin::livewire.central.new-site', [
            'customers' => User::query()
                ->whereHas('roles', fn ($query) => $query->where('name', 'مشتری'))
                ->orderByDesc('created_at')->get(),
        ]);
    }
}
