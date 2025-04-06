<?php

namespace Modules\Admin\Livewire\Central;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

class NewSiteCreateOrUpdate extends Component
{
    public $fetchData = [];
    public $form = [
        'disabled' => false,
    ];

    #[Validate(attribute: [
        'form.dietary_category_id' => 'نام گروه',
        'form.key_name' => 'نام کلید',
    ])]
    protected function rules(): array
    {
        $rules = [
            'form.name' => 'required',
            'form.dietary_category_id' => 'required',
            'form.key_name' => 'required|unique:dietary_restrictions,key_name',
        ];
        if ($this->dietaryRestriction !== null) {
            $rules['form.key_name'] = 'required|unique:dietary_restrictions,key_name,' . $this->dietaryRestriction?->id;
        }
        return $rules;
    }

    public function createOrUpdate()
    {
        dd("sa");
    }

    #[Layout('admin::layouts.central.app')]
    public function render()
    {
        return view('admin::livewire.central.new-site');
    }
}
