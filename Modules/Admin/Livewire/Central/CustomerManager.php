<?php

namespace Modules\Admin\Livewire\Central;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\Admin\Support\CustomerCreator;
use Modules\User\Entities\User;

class CustomerManager extends Component
{
    public bool $showCustomerModal = false;

    public array $customerForm = [
        'first_name' => '', 'last_name' => '', 'center_name' => '',
        'mobile' => '', 'email' => '', 'password' => '', 'password_confirmation' => '',
    ];

    protected function rules(): array
    {
        return [
            'customerForm.first_name' => ['required', 'string', 'max:100'],
            'customerForm.last_name' => ['required', 'string', 'max:100'],
            'customerForm.center_name' => ['required', 'string', 'max:255'],
            'customerForm.mobile' => ['required', 'string', 'max:20', 'unique:users,mobile'],
            'customerForm.email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'customerForm.password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
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
        ];
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
        CustomerCreator::create($this->validate()['customerForm']);
        $this->reset('customerForm', 'showCustomerModal');
        session()->flash('success', 'مشتری با موفقیت ایجاد شد.');
    }

    #[Layout('admin::layouts.central.app')]
    public function render()
    {
        return view('admin::livewire.central.customers', [
            'customers' => User::query()
                ->whereHas('roles', fn ($query) => $query->where('name', 'مشتری'))
                ->latest()->get(),
        ]);
    }
}
