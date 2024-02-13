<?php

namespace Modules\AppointmentSetting\Livewire\AddAppointment;

use Livewire\Component;
use Modules\User\Entities\User;
use Spatie\Permission\Models\Role;
use Modules\User\Enum\UserMetaEnum;

class DocAndSectionList extends Component
{
    public array $search = [];
    public $modalDate = null;
    public function searchDoctors()
    {
        $this->render();
    }
    public function searchSection()
    {
        $this->render();
    }

    //pass data to the modal after doctor has been selected
    public function lunchDocModal(User $doctorId)
    {
        $this->dispatch('lunchModal', true);
        $this->modalDate = $doctorId;
    }


    //select section from modal
    public function addAppointment($sectionId)
    {
        return redirect()->route('admin.appointment.add.setTime',['doctorId' => $this->modalDate->id , 'sectionId' => $sectionId]) ;
    }
    public function render()
    {
        $doctors = Role::find(3)->users()
            ->when(isset($this->search['doctors']) && !empty($this->search['doctors']), function ($query) {
                return $query->whereHas('metas', function ($q) {
                    $q->where([
                        ['meta_key', UserMetaEnum::FIRST_NAME],
                        ['meta_value', 'LIKE', "%{$this->search['doctors']}%"],
                    ]);
                });
            })->orderByDesc('id')->get();

        $setctions = null;
        return view('appointmentsetting::livewire.add-appointment.doc-and-section-list', [
            'doctors' => $doctors,
            'sections' => $setctions,
        ]);
    }
}
