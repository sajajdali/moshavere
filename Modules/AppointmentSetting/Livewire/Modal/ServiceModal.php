<?php

namespace Modules\AppointmentSetting\Livewire\Modal;

use Carbon\Carbon;
use Livewire\Component;

class ServiceModal extends Component
{

    public function addAppointment($date)
    {
        $date = Carbon::now();
        return redirect()->route('admin.appointment.add.specificday', ['date' => $date]);
    }
    public function render()
    {
        return view('appointmentsetting::livewire.modal.service-modal');
    }
}
