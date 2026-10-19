<?php

namespace Modules\User\Livewire\Admin\User;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Modules\User\Entities\User;
use Modules\User\Support\PatientProfile;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * افزودن مراجع: ساخت کاربر با نقش پیش فرض (مراجعه کننده) و تکمیل پرونده او.
 * با کامل شدن شماره موبایل، کاربر موجود پیدا می شود و اطلاعات پرونده اش برای ویرایش بارگذاری می شود.
 */
class AddPatient extends Component
{
    use AuthorizesRequests;

    public array $form = [];

    /** کاربر پیدا شده با شماره موبایل واردشده: id, name, roles, can_edit */
    public ?array $existing = null;

    public bool $mobileChecked = false;

    public function mount(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->form = [
            'mobile' => '',
            'first_name' => '',
            'last_name' => '',
            'national_code' => '',
            'birth_date' => '',
            'father_name' => '',
            'marital_status' => '',
            'acquaintance' => '',
            'role_id' => $this->defaultRole()?->id,
        ];
    }

    /** فقط رقم (ارقام فارسی و عربی هم به انگلیسی تبدیل می شوند) با حداکثر طول مشخص. */
    private function digits(mixed $value, int $max): string
    {
        return substr(preg_replace('/\D/', '', (string) convert2english((string) $value)) ?? '', 0, $max);
    }

    private function defaultRole(): ?Role
    {
        return Role::whereHas('permissions', fn ($q) => $q->whereName('USER_DEFAULT'))->first();
    }

    /** نقش های قابل انتخاب (نقش های کاربری)؛ بدون دسترسی کامل کاربران فقط نقش پیش فرض مجاز است. */
    public function availableRoles()
    {
        $roles = Permission::whereName('USER_ACCESS')->first()?->roles ?? collect();
        if (auth()->user()?->cannot('user')) {
            $default = $this->defaultRole();

            return $roles->where('id', $default?->id)->values();
        }

        return $roles;
    }

    public function updatedForm($value, $key): void
    {
        if ($key === 'mobile') {
            $this->form['mobile'] = $this->digits($value, 11);
            $this->lookup();
        }
        if ($key === 'national_code') {
            $this->form['national_code'] = $this->digits($value, 10);
        }
    }

    /** جستجوی کاربر با شماره موبایل کامل. */
    private function lookup(): void
    {
        $previous = $this->existing;
        $this->existing = null;
        $this->mobileChecked = false;
        $this->resetErrorBag();

        if (strlen($this->form['mobile']) !== 11) {
            if ($previous) {
                $this->clearProfile();
            }

            return;
        }

        $this->mobileChecked = true;
        $user = User::with(['metas', 'roles'])->where('mobile', $this->form['mobile'])->first();
        if (! $user) {
            if ($previous) {
                $this->clearProfile();
            }

            return;
        }

        $canEdit = (bool) auth()->user()?->can('update', $user);
        $this->existing = [
            'id' => $user->id,
            'name' => trim($user->first_name.' '.$user->last_name),
            'roles' => $user->roles->pluck('name')->all(),
            'can_edit' => $canEdit,
        ];

        if ($canEdit) {
            $this->form['first_name'] = (string) $user->first_name;
            $this->form['last_name'] = (string) $user->last_name;
            foreach (PatientProfile::load($user) as $field => $value) {
                $this->form[$field] = (string) ($value ?? '');
            }
        } else {
            $this->clearProfile();
        }
    }

    private function clearProfile(): void
    {
        foreach (array_merge(['first_name', 'last_name'], PatientProfile::FIELDS) as $field) {
            $this->form[$field] = '';
        }
    }

    public function birthAge(): ?int
    {
        return PatientProfile::age((string) ($this->form['birth_date'] ?? ''));
    }

    protected function rules(): array
    {
        $existingId = $this->existing['id'] ?? null;
        $roleIds = $this->availableRoles()->pluck('id')->all();

        return [
            'form.mobile' => ['required', 'digits:11'],
            'form.first_name' => ['required', 'string', 'max:225'],
            'form.last_name' => ['required', 'string', 'max:225'],
            'form.national_code' => ['required', 'digits:10', Rule::unique('users', 'national_code')->ignore($existingId)],
            'form.birth_date' => ['required', function ($attribute, $value, $fail) {
                if (PatientProfile::parseBirthDate((string) $value) === null) {
                    $fail('تاریخ تولد را به صورت شمسی و صحیح وارد کنید (مثلاً 1370/05/21).');
                }
            }],
            'form.father_name' => ['required', 'string', 'max:100'],
            'form.marital_status' => ['required', Rule::in(array_keys(PatientProfile::MARITAL_STATUSES))],
            'form.acquaintance' => ['required', Rule::in(PatientProfile::ACQUAINTANCE_OPTIONS)],
            'form.role_id' => $existingId ? ['nullable'] : ['required', Rule::in($roleIds)],
        ];
    }

    protected function messages(): array
    {
        return [
            'form.mobile.required' => 'وارد کردن شماره موبایل الزامی است',
            'form.mobile.digits' => 'شماره موبایل باید ۱۱ رقم باشد',
            'form.first_name.required' => 'وارد کردن نام الزامی است',
            'form.last_name.required' => 'وارد کردن نام خانوادگی الزامی است',
            'form.national_code.required' => 'وارد کردن کد ملی الزامی است',
            'form.national_code.digits' => 'کد ملی باید ۱۰ رقم باشد',
            'form.national_code.unique' => 'این کد ملی قبلاً برای مراجع دیگری ثبت شده است',
            'form.birth_date.required' => 'وارد کردن تاریخ تولد الزامی است',
            'form.father_name.required' => 'وارد کردن نام پدر الزامی است',
            'form.father_name.max' => 'نام پدر حداکثر ۱۰۰ کاراکتر باشد',
            'form.marital_status.required' => 'وضعیت تأهل را انتخاب کنید',
            'form.marital_status.in' => 'وضعیت تأهل انتخاب‌شده معتبر نیست',
            'form.acquaintance.required' => 'نحوه آشنایی با مرکز را انتخاب کنید',
            'form.acquaintance.in' => 'نحوه آشنایی انتخاب‌شده معتبر نیست',
            'form.role_id.required' => 'نقش کاربر را انتخاب کنید',
            'form.role_id.in' => 'نقش انتخاب‌شده مجاز نیست',
        ];
    }

    public function save(bool $addAnother = false)
    {
        // شماره باید همین الان دوباره بررسی شود تا کاربری که بعد از آخرین جستجو ساخته شده، دوباره ساخته نشود
        $this->form['mobile'] = $this->digits($this->form['mobile'], 11);
        if (strlen($this->form['mobile']) === 11 && ($this->existing['id'] ?? null) !== User::where('mobile', $this->form['mobile'])->value('id')) {
            $this->lookup();
        }

        if ($this->existing && ! $this->existing['can_edit']) {
            $this->addError('form.mobile', 'این شماره قبلاً ثبت شده و شما اجازه ویرایش این کاربر را ندارید.');

            return null;
        }

        $this->form = array_merge($this->form, PatientProfile::normalize($this->form));
        $this->validate();

        $isUpdate = $this->existing !== null;
        $user = DB::transaction(function () use ($isUpdate) {
            if ($isUpdate) {
                $user = User::findOrFail($this->existing['id']);
                $this->authorize('update', $user);
            } else {
                $this->authorize('create', User::class);
                $user = User::create([
                    'mobile' => $this->form['mobile'],
                    'password' => User::generatePassword(),
                ]);
                $user->creator = auth()->id();
                $role = $this->availableRoles()->firstWhere('id', (int) $this->form['role_id']);
                if ($role) {
                    $user->syncRoles([$role]);
                }
            }

            $user->first_name = $this->form['first_name'];
            $user->last_name = $this->form['last_name'];
            PatientProfile::save($user, $this->form);

            return $user;
        });

        $message = ($isUpdate ? 'پرونده مراجع «' : 'مراجع «').trim($user->first_name.' '.$user->last_name).($isUpdate ? '» به‌روزرسانی شد.' : '» با موفقیت اضافه شد.');
        session()->flash('success', $message);

        if ($addAnother) {
            $this->reset('existing', 'mobileChecked');
            $this->resetForm();
            $this->dispatch('patient-saved');

            return null;
        }

        return redirect()->route('admin.user.index');
    }

    public function render()
    {
        return view('user::livewire.admin.user.add-patient', [
            'roles' => $this->availableRoles(),
            'defaultRoleId' => $this->defaultRole()?->id,
        ])->title('افزودن مراجع');
    }
}
