<?php

namespace Modules\AppointmentUser\Livewire\Admin\AddAppointment\Modal;

use Livewire\Component;
use Modules\Place\app\Models\Place;
use Modules\User\Entities\User;
use Livewire\Attributes\Url;
use Modules\User\Enum\UserMetaEnum;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

class ServiceAndDoctorModal extends Component
{
    /*
    this modal is beeing used in :
    SpecificDayAvailableAppointment::class
    */
    public $step = 1;

    public array $fetchData = [];
    public array $form = [];

    #[Url]
    public array $search =  [
        'doctor' => null,
        'service' => null,
    ];

    public $docSection;

    public function closeModal()
    {
        $this->step = 1;
        $this->search = [
            'doctor' => null,
            'service' => null,
        ];
        $this->form = [];
        $this->docSection = null;
        $this->fetchData['service'] = collect();
        $this->fetchData['places'] = collect();
        $this->resetValidation();
    }

    //after seleced one of the doctors
    public function doctorSelected($id)
    {
        $doctor = User::doctors_query()->whereKey($id)->first();
        if (! $doctor) {
            return $this->addError('selectDoctor', 'پزشک انتخاب‌شده معتبر نیست.');
        }

        $this->docSection = $doctor;
        $this->fetchData['service'] = $doctor->service()->orderBy('parent_id')->orderBy('title')->get();
        $this->fetchData['places'] = collect();
        $this->form = [];
        $this->search['service'] = null;
        $this->resetValidation();
        $this->step = 2;
    }
    //after seleced one of the services
    //final function
    public function selectSection()
    {
        if (! isset($this->form['service']) || ! $this->docSection) {
            return $this->addError('selectService', 'لطفا یک سرویس را انتخاب کنید');
        }

        $service = $this->docSection->service()->whereKey($this->form['service'])->first();
        if (! $service) {
            return $this->addError('selectService', 'سرویس انتخاب‌شده برای این پزشک معتبر نیست.');
        }

        $this->form['service_id'] = $service->id;
        $this->form['doctor_id'] = $this->docSection->id;

        $placeIds = AppointmentSetting::query()
            ->where('user_id', $this->form['doctor_id'])
            ->where('service_id', $this->form['service_id'])
            ->whereNotNull('place_id')
            ->pluck('place_id');

        if ($placeIds->isNotEmpty()) {
            $this->fetchData['places'] = Place::active()->whereIn('id', $placeIds)->orderBy('title')->get();
        } else {
            $this->fetchData['places'] = $this->docSection->places()->active()->orderBy('title')->get();
            if ($this->fetchData['places']->isEmpty()) {
                $this->fetchData['places'] = Place::active()->orderBy('title')->get();
            }
        }

        if ($this->fetchData['places']->isEmpty()) {
            $this->step = 3;
            return $this->addError('selectPlace', 'مطب فعالی برای این پزشک و بخش یافت نشد.');
        }

        if ($this->fetchData['places']->count() === 1) {
            return  $this->selectplace($this->fetchData['places']->first()->id);
        }

        $this->resetValidation();
        $this->step = 3;
    }
    public function selectplace($id)
    {
        if (! collect($this->fetchData['places'] ?? [])->contains('id', (int) $id)) {
            return $this->addError('selectPlace', 'مطب انتخاب‌شده معتبر نیست.');
        }

        $this->form['place_id'] = $id;
        $special_setting_for_appointment = AppointmentSetting::SpecialOrGeneralSetting(
            $this->form['doctor_id'],
            $this->form['service_id'],
            $this->form['place_id']
        );

        if (!empty($special_setting_for_appointment)) {
            $this->form['app_id'] = $special_setting_for_appointment->id;
            if ($special_setting_for_appointment->segments()->exists()) {
                $segment = $special_setting_for_appointment->segments()->with([
                    'items' => fn ($query) => $query->orderBy('priority')->orderBy('id'),
                ])->first();

                $this->fetchData['segmentItems'] = $segment?->items ?? collect();
                $this->fetchData['segmentSingleChoice'] = (bool) $segment?->multiple_choice;
                $this->form['segments'] = [];
                $this->resetValidation();
                $this->step = 4;
                return;
            }

            return $this->completeSelection();
        } else {
            return redirect()->route('admin.appointment_user.addApp')->with('error', 'تنظیمات حضور یافت نشد');
        }
    }

    public function selectSegment($segmentItemId = null)
    {
        $segmentItems = collect($this->fetchData['segmentItems'] ?? []);

        if ($this->fetchData['segmentSingleChoice'] ?? false) {
            if (! $segmentItems->contains('id', (int) $segmentItemId)) {
                return $this->addError('selectSegment', 'زیربخش انتخاب‌شده معتبر نیست.');
            }
            $selectedIds = [(int) $segmentItemId];
        } else {
            $selectedIds = collect($this->form['segments'] ?? [])
                ->filter(fn ($selected) => (bool) $selected)
                ->keys()
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $segmentItems->contains('id', $id))
                ->values()
                ->all();

            if (empty($selectedIds)) {
                return $this->addError('selectSegment', 'حداقل یک زیربخش را انتخاب کنید.');
            }
        }

        $this->form['segment_item_ids'] = implode(',', $selectedIds);

        return $this->completeSelection();
    }

    private function completeSelection()
    {
        $this->dispatch('closeModal', true);
        $this->dispatch(
            'docHasChange',
            appId: $this->form['app_id'],
            serviceId: $this->form['service_id'],
            placeId: $this->form['place_id'],
            segmentItemId: $this->form['segment_item_ids'] ?? null,
        );
    }
    public function searchDocAndSection()
    {
        if ($this->step == 2) {
            $this->fetchData['service'] = $this->docSection->service()->when(!empty($this->search['service']), function ($q) {
                return $q->where('title', 'LIKE', "%{$this->search['service']}%");
            })->orderBy('parent_id')->orderBy('title')->get();
        }
    }
    //close modal button
    public function ignoreSearch()
    {
        $this->search = [
            'doctor'  => null,
            'service' => null,
        ];
        if ($this->step == 2) {
            $this->fetchData['service'] = $this->docSection->service()->orderBy('parent_id')->orderBy('title')->get();
        }
    }

    public function previousStep()
    {
        $this->resetValidation();

        if ($this->step === 4) {
            unset($this->form['segments'], $this->form['segment_item_ids']);
            $this->step = collect($this->fetchData['places'] ?? [])->count() > 1 ? 3 : 2;
            return;
        }

        if ($this->step === 3) {
            unset($this->form['place_id']);
            $this->step = 2;
            return;
        }

        if ($this->step === 2) {
            $this->form = [];
            $this->docSection = null;
            $this->fetchData['service'] = collect();
            $this->fetchData['places'] = collect();
            $this->search['service'] = null;
            $this->step = 1;
        }
    }

    public function mount()
    {
        $this->fetchData['service'] = collect();
        $this->fetchData['places'] = collect();
    }
    public function render()
    {
        if ($this->step == 1) {
            if (User::doctors()->count() == 1) {
                $this->doctorSelected(User::doctors()->first()->id);
            }
            $query = User::doctors_query()->when(!empty($this->search['doctor']), function ($query) {
                return $query->where(function ($q) {
                    $q->whereHas('metas', function ($q) {
                        $q->where([
                            ['meta_key', UserMetaEnum::FIRST_NAME],
                            ['meta_value', 'LIKE', "%{$this->search['doctor']}%"],
                        ]);
                    })->orWhereHas('metas', function ($q) {
                        $q->where([
                            ['meta_key', UserMetaEnum::LAST_NAME],
                            ['meta_value', 'LIKE', "%{$this->search['doctor']}%"],
                        ]);
                    });
                });
            })->orderBy('id')->take(30)
                ->with('metas')
                ->get();
        } else {
            $query = User::doctors()->take(30);
        }

        return view(
            'appointmentuser::livewire.admin.add-appointment.modal.service-and-doctor-modal',
            ['doctors' =>  $query]
        );
    }
}
