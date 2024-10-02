<?php

namespace Modules\AppointmentUser\Livewire\Admin;

use App\Enum\ActiveEnum;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\User\Entities\User;
use Spatie\Permission\Models\Role;
use Modules\Place\app\Models\Place;
use Modules\User\Enum\UserMetaEnum;
use Modules\Service\app\Models\Service;

class AppointmentUserCreateOrUpdate extends Component
{
    use WithPagination;
    #[Locked]
    public bool $permitionCheck ; 
    public array $search = [];
    public array $form = [
        'doctorSelected'    => null,
        'doctorServices' => [],
        'modalSelectedData' => [
            'doctor' => null,
            'serviec' => null,
            'place' => null,
        ]
    ];
    public array $fetchData = [];
    public $modalDate = null;
    public function searchDoctors()
    {
        $this->render();
    }
    public function ignoreSearch()
    {
        $this->search = [];
        $this->render();
    }
    public function searchService()
    {

        $this->render();
    }

    //if user lunch modal from doctor section
    public function docSelected(User $user)
    {
        $this->fetchData = [];
        $this->form['modalSelectedData']['serviec'] = null;
        $this->form['modalSelectedData']['place']   = null;
        $this->form['modalSelectedData']['doctor'] = $user->id;
        if (AppointmentSetting::where('user_id', $user->id)->exists()) {
            $this->fetchData['placeList'] = $user->places;
            //check if there is more than 1 place exist
            if (count($this->fetchData['placeList']) == 1) {
                $this->form['modalSelectedData']['place'] = $this->fetchData['placeList']->first()->id;
                if (
                    !empty($this->form['modalSelectedData']['doctor']) &&
                    !empty($this->form['modalSelectedData']['place'])
                ) {
                    $doctor = User::find($this->form['modalSelectedData']['doctor']);
                    $this->fetchData['ServiceList'] = $doctor->activeServices();
                    //check if there is more than 1 service exist
                    if (count($this->fetchData['ServiceList']) == 1) {
                        return redirect()->route(
                            'admin.appointment.add.setTime',
                            [
                                'doctorId' =>  $this->form['modalSelectedData']['doctor'],
                                'sectionId' => $this->fetchData['ServiceList']->first()->id,
                                'placeId' => $this->form['modalSelectedData']['place']
                            ]
                        );
                    } else {
                        return  $this->lunchmodal('serviceModal');
                    }
                }
                return  $this->lunchmodal('docModal');
            } else {
                $this->lunchmodal('placeModal');
            }
            // if()
        } else {
            return redirect()->route('admin.appointment.doctor.list')->with('error', " تنظیمات روز های حضور برای {$user->fullName} تعریف نشده است");
        }
    }

    public function placeSelected(Place $place)
    {
        $this->form['modalSelectedData']['place'] = $place->id;
        if (
            !empty($this->form['modalSelectedData']['doctor']) &&
            !empty($this->form['modalSelectedData']['place'])
        ) {
            $doctor = User::find($this->form['modalSelectedData']['doctor']);
            $this->fetchData['ServiceList'] = $doctor->service;
            if (count($this->fetchData['ServiceList']) == 1) {
                $this->dispatch('show-loading',true);
                return redirect()->route(
                    'admin.appointment.add.setTime',
                    [
                        'doctorId' =>  $this->form['modalSelectedData']['doctor'],
                        'sectionId' => $this->fetchData['ServiceList']->first()->id,
                        'placeId' => $this->form['modalSelectedData']['place']
                    ]
                );
            } else {
                return  $this->lunchmodal('serviceModal');
            }
        }
        if (count($this->fetchData['docList']) == 1) {
            return $this->docSelectedFrommodal($this->fetchData['docList']->first());
        }
        return  $this->lunchmodal('docModal');
    }
    public function serviceSelected(Service $service)
    {
        $this->dispatch('show-loading',true);
        return redirect()->route(
            'admin.appointment.add.setTime',
            [
                'doctorId' =>  $this->form['modalSelectedData']['doctor'],
                'sectionId' => $service->id,
                'placeId' => $this->form['modalSelectedData']['place']
            ]
        );
    }
    //if user lunch modal from service section
    public function serviceSelectedFromServiceSection(Service $service)
    {
        $this->fetchData = [];
        $this->form['modalSelectedData']['place']   = null;
        $this->form['modalSelectedData']['doctor']  = null;
        $this->form['modalSelectedData']['service'] = $service->id;
        $this->fetchData['docList'] = $service->user;
        $associatedService = Service::with('user.places')->find($service->id);
        $this->fetchData['placeList'] = $associatedService->user->flatMap->activePlaces()->unique('id');
        if (count($this->fetchData['placeList']) == 1) {
            $this->placeSelected($this->fetchData['placeList']->first());
        } else {
            $this->lunchModal('placeModal');
        }
    }
    public function docSelectedFrommodal(User $user)
    {
        $this->form['modalSelectedData']['doctor'] = $user->id;
        if (
            !empty($this->form['modalSelectedData']['place']) &&
            !empty($this->form['modalSelectedData']['doctor']) &&
            !empty($this->form['modalSelectedData']['service'])
        ) {
            $this->dispatch('show-loading',true);
            return redirect()->route(
                'admin.appointment.add.setTime',
                [
                    'doctorId' =>  $this->form['modalSelectedData']['doctor'],
                    'sectionId' => $this->form['modalSelectedData']['service'],
                    'placeId' => $this->form['modalSelectedData']['place']
                ]
            );
        }
    }
    private function lunchmodal($name)
    {
        $this->dispatch('lunchmodal', name: $name);
    }


    public function mount()
    {
      
        if (!Service::exists()) {
            return redirect()->route('admin.service.list')->with('error', 'لطفا حداقل یک بخش به سیستم اضافه کنید');
        }
    }
    public function render()
    {
        $permitionCheck = auth()->user();
        $permitionCondition = !$permitionCheck->isAdmin() && $permitionCheck->hasRole('پزشک') ; 
        $docQuery = User::doctors_query()->when( $permitionCondition,function($q){
                return $q->where('id',auth()->user()->id);
        });
        if (isset($docQuery)) {
            $docQuery =  $docQuery->where(function ($query) {
                $query->whereDoesntHave('metas', function ($q) {
                    $q->where('meta_key', UserMetaEnum::BAN_USER)
                        ->where('meta_value', true);
                });
            })->when(isset($this->search['doctors']) && !empty($this->search['doctors']), function ($query) {
                return $query->where(function ($q) {
                    $q->whereHas('metas', function ($q) {
                        $q->where([
                            ['meta_key', UserMetaEnum::FIRST_NAME],
                            ['meta_value', 'LIKE', "%{$this->search['doctors']}%"],
                        ]);
                    })->orWhereHas('metas', function ($q) {
                        $q->where([
                            ['meta_key', UserMetaEnum::LAST_NAME],
                            ['meta_value', 'LIKE', "%{$this->search['doctors']}%"],
                        ]);
                    });
                });
            })->orderByDesc('id')->paginate(20);
        }
        $Services = Service::when( $permitionCondition,function($q){
            return $q->whereHas('user',function($qq){
                 $qq->where('users.id',auth()->user()->id);
            });
        })->where('active', ActiveEnum::ACTIVE)
            ->when(isset($this->search['searchService']) && !empty($this->search['searchService']), function ($query) {
                return $query->where('title', 'LIKE', "%{$this->search['searchService']}%");
            })->orderByDesc('id')->paginate(20);
        return view('appointmentuser::livewire.admin.appointment-user-create-or-update', [
            'doctors' => $docQuery,
            'Services' => $Services,
        ]);
    }
}
