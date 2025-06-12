<?php

namespace Modules\Service\Livewire;

use Livewire\Component;
use App\Enum\ActiveEnum;
use App\trait\UploadFile;
use Modules\User\Entities\User;
use Spatie\Permission\Models\Role;
use Modules\Place\app\Models\Place;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Modules\Service\app\Models\Service;
use Modules\Service\Enum\ServiceShowTypeEnum;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class CreateOrUpdate extends Component
{
    use WithFileUploads , UploadFile;

    protected $filePath = 'service';

    public ?Service $service;
    public $isEdited = false;
    public array $form = [
        'parent_id' => null,
        'doctors'   => [],
        'show_type' => true,
        'place'     => null,
    ];
    public array $fetchdata = [];

    public function rules()
    {
        return [
            'form.title'     => 'required|string|max:225',
            'form.parentId'  => 'nullable|integer',
            'form.priority'  => 'required|integer',
            'form.img'       => 'nullable',
            'form.active'    => 'nullable',
            'form.show_type' => 'nullable',
            'form.place'     => 'nullable',
        ];
    }

    public function createOrUpdateSection()
    {
        $this->validate();
        if(! app()->environment('local')){
            Cache::forget('most_viewed_service');
        }
        //data for update Or create Service
        $parentId = ($this->form['parent'] == 0 || null) ? null : $this->form['parent'];
        $active = $this->form['active'] == 'true' ? 1 : 0;
        $modelCreateOrUpdate = [
            'title'         => $this->form['title']         ?? '',
            'parent_id'     => $parentId,
            'api_code'     => $this->form['api_code']      ?? '',
            'priority'      => $this->form['priority']   ?? 1,
            'active'        => ActiveEnum::tryFrom($active),
            'show_type'     => $this->form['show_type'] ? ServiceShowTypeEnum::SHOW : ServiceShowTypeEnum::DONT_SHOW,
            'detail'        => [
                Service::APP_QUESTION_TITLE => null,
                Service::NOT_SHOW_TO_USER   => false
            ]
        ];

        // مدیریت تصویر
        if (!empty($this->form['photo'])) {
            $finalPath = str_replace('temp/', '', $this->form['photo']);
            Storage::disk('tenant')->move($this->form['photo'], $finalPath);
            $modelCreateOrUpdate['icon'] = $finalPath;
        } else {
            $modelCreateOrUpdate['icon'] = null;
        }

        if (isset($this->form['qestion']) && $parentId == null) {
            $modelCreateOrUpdate['detail'] = [
                Service::APP_QUESTION_TITLE => $this->form['qestion'],
            ];
        }
        if (isset($this->form['notShowToUser'])) {
            $modelCreateOrUpdate['detail'][Service::NOT_SHOW_TO_USER] =   $this->form['notShowToUser'];
        }
        if ($this->isEdited) {
            $this->service->update($modelCreateOrUpdate);
            $msg = 'بخش با موفقیت ویرایش شد';
        } else {
            $msg = 'بخش با موفقیت اضافه شد';
            $this->service =  Service::create($modelCreateOrUpdate);
        }
        //add doctors to Service
        if (isset($this->form['doctors'])) {
            $syncArr = [];
            foreach ($this->form['doctors'] as $userId => $value) {
                //check if check box checked
                if ($value) {
                    $syncArr[] = $userId;
                }
            }
            $this->service->user()->sync($syncArr);
        }
        if (isset($this->form['place'])) {
            $this->service->place()->sync($this->form['place']);
        }
        return redirect()->route('admin.service.list')->with('success', $msg);
    }

    private function addInitialValues()
    {

        $this->form['api_code']     = $this->service->api_code;
        $this->form['title']     = $this->service->title;
        $this->form['parent'] = $this->service->parent_id;
        $this->form['place']     = $this->service->place()?->pluck('places.id')->toArray();
        $this->form['img']       = $this->service->icon;
        $this->form['priority']  = $this->service->priority;
        $this->form['active']    =  $this->service->active == ActiveEnum::ACTIVE ? true : false;
        $this->form['show_type']    =  $this->service->show_type == ServiceShowTypeEnum::SHOW ? true : false;
        if (isset($this->service->detail[Service::APP_QUESTION_TITLE])) {
            $this->form['qestion'] = $this->service->detail[Service::APP_QUESTION_TITLE];
        }
        if (isset($this->service->detail[Service::NOT_SHOW_TO_USER])) {
            $this->form['notShowToUser'] = $this->service->detail[Service::NOT_SHOW_TO_USER];
        }
        $doctors =  $this->service->user->pluck('id')->toArray();
        foreach ($doctors as $doc) {
            $this->form['doctors'][$doc] =  true;
        }
    }
    public function mount()
    {
        $service = request()->route('service');
        if ($service instanceof Service) {
            $this->service           =  $service;
            $this->isEdited          = true;
            $this->addInitialValues();

            //  load  image
            $this->photo = $service->icon;
            $path = $service->icon ;
            if ($path) {
                $this->uploadedPhotoUrl = Storage::disk('tenant')->url($path);
                $this->uploadedFileName = basename($path);
                $this->uploadedFileType = getFileIconClass(pathinfo($path, PATHINFO_EXTENSION));
            } else {
                $this->uploadedPhotoUrl = null;
            }
        } else {
            $this->form['priority']      = Service::maxPriority();
            $this->form['active']        = 'true';
        }



        $this->fetchdata['doctors']  = User::doctors();
        $this->fetchdata['services'] = Service::whereNull('parent_id')->get();
        $this->fetchdata['places']   = Place::active()->get();
    }
    public function render()
    {
        return view('service::livewire.create-or-update');
    }
}
