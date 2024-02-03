<?php

namespace Modules\Speciality\Livewire;

use Livewire\Component;
use Livewire\Attributes\Rule;
use Modules\User\Entities\User;
use Spatie\Permission\Models\Role;
use Modules\Speciality\app\Models\Speciality;
use Modules\Speciality\app\Models\SpecialityUser;
use Modules\Speciality\Enum\SpecialityStatusEnum;

class UpdateOrCreateSpeciality extends Component
{
    #[Rule('required|string')]
    public $specialityTitle;
    #[Rule('required|integer')]
    public $priority;
    public $status = 'true';
    public $message;
    public $doctors;
    public array $doctor = [];

    public Speciality $speciality;

    public function createSpeciality()
    {
        $this->validate();
        $status = $this->status == 'true' ? SpecialityStatusEnum::ACTIVE : SpecialityStatusEnum::DEACTIVE;
        if (isset($this->speciality)) {
            $this->speciality->update([
                'title' => $this->specialityTitle,
                'status' => $status,
                'priority' => $this->priority,
            ]);
            $this->message = 'تخصص با موفقیت اضافه شد';
        } else {
            $this->speciality = Speciality::create([
                'title' => $this->specialityTitle,
                'status' => $status,
                'priority' => $this->priority,
            ]);
            $this->message = 'تخصص با موفقیت اضافه شد';
        }
        if (isset($this->doctor)) {
            $syncArr = [];
            foreach ($this->doctor as $userId => $value) {
                //check if check box checked
                if ($value) {
                    $syncArr[] = $userId;
                }
            }
            $this->speciality->user()->sync($syncArr);
        }
        $this->doctor = [];
        $this->specialityTitle = null;
        $this->priority + 1;
        return redirect(route('admin.speciality.index'))->with('success', 'تغییرات با موفقیت ذخیره شد');
    }

    public function mount()
    {
        $this->priority =  Speciality::maxOrder();
        $seciality = request()->route('speciality');
        if ($seciality instanceof Speciality) {
            $this->speciality = $seciality;
            $this->specialityTitle  = $this->speciality->title;
            $this->priority  = $this->speciality->priority;
            if ($this->speciality->status == SpecialityStatusEnum::ACTIVE) {
                $this->status  = 'true';
            }
            $doctorThatHasThisSpeciality = $seciality->user()->get();
            if ($doctorThatHasThisSpeciality->isNotEmpty()) {
                foreach ($doctorThatHasThisSpeciality as $value) {
                    $this->doctor[$value->id] = 'true';
                }
            }
        } else {
            $this->status  = 'false';
        }
        $this->doctors = Role::find(3)->users;
    }
    public function render()
    {

        return view('speciality::livewire.update-or-create-speciality');
    }
}
