<?php

namespace Modules\Absence\Livewire;

use Livewire\Component;
use Illuminate\Support\Arr;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Illuminate\Validation\Rule;
use Modules\AppointmentSetting\app\Jobs\CacheJob;
use Modules\AppointmentSetting\app\Jobs\createCacheJob;
use Modules\User\Entities\User;
use Spatie\Permission\Models\Role;
use Modules\User\Enum\UserMetaEnum;
use Hekmatinasser\Verta\Facades\Verta;
use Modules\Absence\app\Models\Absence;
use Modules\Service\app\Models\Service;

class AbsenceRegistration extends Component
{


    public $step = 1;
    #[Url]
    public $search = [];
    public $searchPanel = '';
    //property for showing time sections
    public $counter = [
        'number' => 1,
    ];
    public array $fetchData = [];
    public array $form = [];

    public function addCounter($obj)
    {
        $this->counter[$obj] = $this->counter[$obj] + 1;
        $this->render();
        $this->addjsclasses();
    }
    public function removeCounter($obj)
    {
        $this->counter[$obj] = $this->counter[$obj] - 1;
        if ($obj == 'number') {
            //remove the last array for validation
            if (isset($this->form['absence'])) {
                unset($this->form['absence'][(count($this->form['absence']) - 1)]);
            }
        }
        $this->render();
        $this->addjsclasses();
    }
    public function ChangeCheckBoxesStatus($checkbox, $status)
    {
        // Update the checkbox value

        $this->form['doctor'][$checkbox] = $status;
        // dd($this->form);
    }
    private function customCheckBoxValidate()
    {

        $validate = false;
        if (isset($this->form['doctor'])) {
            foreach ($this->form['doctor'] as $selectedDocs) {
                if (isset($selectedDocs) && $selectedDocs == true) {
                    $validate = true;
                }
            }
        }
        if (!$validate) {
            $this->addError('form.doctor', 'انتخاب پزشک الزامی است');
            return false;
        }
        return true;
    }
    public function AddStep()
    {
        //validate to check atleast one of the doctors selected
        if ($this->customCheckBoxValidate()) {
            $this->resetValidation();
            $this->dispatch('jsloader', true);
            $this->step =  $this->step + 1;
        }
    }
    public function prevStep()
    {
        $this->step =  $this->step - 1;
        $this->dispatch('loadjs', true);
    }
    public function addjsclasses()
    {
        $this->dispatch('jsloader', true);
    }
    public function lunchconfirmModal()
    {
        $validateDate = [];
        if (isset($this->form['absence'])) {
            foreach ($this->form['absence'] as $index => $value) {
                if (isset($value['start']) || isset($value['end'])) {
                    $validateDate["form.absence.{$index}.start"] = 'required';
                    $validateDate["form.absence.{$index}.end"] = 'required';
                }
            }
        } else {
            return $this->addError('form.absence', 'انتخاب تاریخ الزامی است');
        }
        $this->validate($validateDate);

        // assign user model to selected doctors
        $services = [];
        if (count($this->form['doctor']) >= 1) {
            foreach ($this->form['doctor'] as $index =>  $doctor) {
                if ($doctor) {
                    $this->form['doctor'][$index] = User::find($index);
//                    $services[] =  $this->form['doctor'][$index]->service;
                } else {
                    unset($this->form['doctor'][$index]);
                }
            }
        }

        if (count($this->form['doctor']) <= 1) {
            if (isset($this->fetchData['selectedDoctorsSection'])) {
                unset($this->fetchData['selectedDoctorsSection']);
            }
            foreach (Arr::flatten($this->form['doctor'])[0]->service as $service) {
                $this->fetchData['selectedDoctorsSection'][] = [
                    'id'     => $service->id,
                    'title' => $service->title,
                ];
            }
            $this->dispatch('lunchSelectSectionMdal', true);
        } else {
            foreach ($this->form['absence'] as $key => $date) {
                foreach ($this->form['doctor'] as $eachDoctor) {
                    $CreateModel = [
                        'user_id' => $eachDoctor->id,
                        'start_at' =>  Verta::parse($date['start'])->toCarbon(),
                        'end_at'   =>  Verta::parse($date['end'])->toCarbon(),
                    ];
                    $this->createAbsence($CreateModel);
                }
            }

            return redirect()->route('admin.absence.list')->with('success', 'تنظیمات با موفقیت برای شما ذخیره شد');
        }
    }
    private function createAbsence($CreateModel)
    {
        Absence::create($CreateModel);
    }
    public function storeForAllSection()
    {
        foreach ($this->form['absence'] as $key => $date) {
            $doctorSelected = Arr::flatten($this->form['doctor'])[0];

            // manage cache
            foreach ($doctorSelected->appointmentSettings as $appointmentSetting){
                CacheJob::dispatch($appointmentSetting);
            }
            // manage cache

            $CreateModel = [
                'user_id' => $doctorSelected->id,
                'start_at' => Verta::parse($date['start'])->toCarbon(),
                'end_at'   =>  Verta::parse($date['end'])->toCarbon(),
            ];
        }
        $this->createAbsence($CreateModel);
        return redirect()->route('admin.absence.list')->with('success', 'تنظیمات با موفقیت ذخیره شد');
    }
    public function storeForSelectedsections()
    {
        $services = [];
        foreach ($this->form['selectedSection'] as $serviceId => $status) {
            if ($status) {
                foreach ($this->form['absence'] as $key => $date) {

                    $services = [
                        'user_id' => (Arr::flatten($this->form['doctor'])[0])->id,
                        'service_id' => $serviceId,
                        'start_at' => Verta::parse($date['start'])->toCarbon(),
                        'end_at'   =>  Verta::parse($date['end'])->toCarbon(),
                    ];
                    $this->createAbsence($services);
                }
            }
        }

        session()->flash('success', 'تنظیمات با موفقیت ذخیره شد');
        return redirect()->route('admin.absence.list');
    }

    public function searchDoctor()
    {
        $query = Role::find(3)->users();
        $this->fetchData['doctors'] = $query->when(isset($this->search['id']) && !empty($this->search['id']), function ($query) {
            return $query->where('id', 'LIKE', "%{$this->search['id']}%");
        })->when(isset($this->search['mobile']) && !empty($this->search['mobile']), function ($query) {
            return $query->where('mobile', 'LIKE', "%{$this->search['mobile']}%");
        })->when(isset($this->search['first_name']) && !empty($this->search['first_name']), function ($query) {
            return $query->whereHas('metas', function ($q) {
                $q->where([
                    ['meta_key', UserMetaEnum::FIRST_NAME],
                    ['meta_value', 'LIKE', "%{$this->search['first_name']}%"],
                ]);
            });
        })->when(isset($this->search['last_name']) && !empty($this->search['last_name']), function ($query) {
            return $query->whereHas('metas', function ($q) {
                $q->where([
                    ['meta_key', UserMetaEnum::LAST_NAME],
                    ['meta_value', 'LIKE', "%{$this->search['last_name']}%"],
                ]);
            });
        })->get();
    }
    public function resetProperties()
    {
        $this->fetchData['doctors'] = Role::find(3)->users;
        $this->search = [];
    }
    public function mount()
    {
        $this->fetchData['doctors'] = Role::find(3)->users;
    }


    public function render()
    {
        return view('absence::livewire.absence-registration');
    }
}
