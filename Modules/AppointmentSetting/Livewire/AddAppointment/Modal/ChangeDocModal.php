<?php

namespace Modules\AppointmentSetting\Livewire\AddAppointment\Modal;

use Livewire\Component;

class ChangeDocModal extends Component
{
    public $step = 1;

    public function changeDoctor()
    {
        // pass the doctor id with the method
        $this->step = $this->step + 1;
        $this->render();
    }
    public function selectSection() {
        $this->dispatch('closeModal',true);
    }
    public function render()
    {
        return view('appointmentsetting::livewire.add-appointment.modal.change-doc-modal');
    }
}
