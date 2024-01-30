<?php

namespace Modules\User\Livewire\Admin\User;

use Livewire\Component;
use Modules\Diet\Entities\DietPlan;
use Modules\Diet\Entities\DietRequest;
use Modules\User\Entities\User;

class AssignDiet extends Component
{

    public User $user;
    public $suggestedDiet;
    public $selectedDiet;

    public function messages()
    {
        return [
            'selectedDiet.required' => 'لطفا رژیم مورد نظر را انتخاب کنید',
        ];
    }

    public function assignDiet()
    {
        $this->validate([
            'selectedDiet' => 'required'
        ]);
       $diet_plan =  DietPlan::find($this->selectedDiet);
        //create new function for assigning selective diet
        $dietPlan = app('dietService')->insertUserDietPlan($this->user,$diet_plan);
        $rejim = app('dietService')->makeRejim($dietPlan['dietRequestModel'] , DietRequest::ACTION_REQUEST_INSERT);

        session()->flash('success','رژریم با موفقیت برای کابر تجویز شد');
        return redirect()->route('admin.user.document',[$this->user]);
    }
    public function mount($user)
    {
        $this->user = $user;
        $this->findSuggestedDiet();
    }

    public function findSuggestedDiet()
    {
        $conditions = app('dietService')->getUserConditions($this->user);
        $dietPlans  = app('dietService')->getDietPlans($conditions);
        $this->suggestedDiet = $dietPlans->first()?->id;
        $this->selectedDiet = $this->suggestedDiet;

    }

    public function render()
    {
        return view('user::livewire.admin.user.assign-diet');
    }
}
