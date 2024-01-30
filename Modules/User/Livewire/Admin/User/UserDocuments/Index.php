<?php

namespace Modules\User\Livewire\Admin\User\UserDocuments;

use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\User\Entities\User;

#[Title("پرونده کاربر")]
// #[Layout('admin::layouts.app')]
class Index extends Component
{
    public $user;
    public $first_name, $last_name, $mobile, $password, $gender;
    public $modaldata;
    public $userBmi;

    public $mmsg = false;
    public $meta_is_json_array = false;
    public function editbasicInfo()
    {
        $this->validate([
            'first_name' => 'required|max:225|string',
            'last_name'  => 'required|max:225|string',
            'mobile'     => 'required|max:225|string',
            'gender'     => 'required|max:225',
            'password'   => 'nullable|max:225|string',
        ]);
        $this->user->first_name = $this->first_name;
        $this->user->last_name  = $this->last_name;
        $this->user->mobile     = $this->mobile;
        $this->user->gender     = $this->gender;
        if ($this->password) {
            $this->user->update(['password' =>  Hash::make($this->password)]);
        }
        $this->mmsg = 'اطلااعات کاربر با موفقیت ویرایش شد';
    }

    public function luchModal($metaName)
    {
        $this->meta_is_json_array = false ;
        $this->modaldata = $this->user->$metaName;
        $this->modaldata->name = $this->user->$metaName?->last()->meta_key->getName();
        if($metaName == 'habits_meta' || $metaName ==  'food_restriction_meta' || $metaName ==  'weaknesses_body_meta') {
            $this->meta_is_json_array = true ;
        }
        $this->dispatch('lunchHistoryModal');
    }
    public function mount(User $user)
    {
        $this->user = $user;
        // dd($this->user->last_name);
        $this->first_name =  $this->user->first_name;
        $this->last_name  =  $this->user->last_name;
        $this->mobile     =  $this->user->mobile;
        $this->gender     =  $this->user->gender;
        // dd($user->gender->meta_key->getOptionName($user->gender->meta_value));
        // $enum = UserMetaEnum::tryFrom($this->user->user_gender?->meta_key->value);
    }

    public function calculateBmi()
    {
        $this->modaldata = null ;
        $this->userBmi = app('dietService')->computeCalorie($this->user);
    }
    public function render()
    {
        return view('user::livewire.admin.user.user-documents.index');
    }
}
