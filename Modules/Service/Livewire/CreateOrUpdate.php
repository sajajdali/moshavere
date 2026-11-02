<?php

namespace Modules\Service\Livewire;

use Livewire\Attributes\Locked;
use Livewire\Component;
use Illuminate\Support\Facades\Gate;
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
    use WithFileUploads, UploadFile;

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

    // کاربر مدیریت بدون «ویرایش بخش»: فقط توضیحات و تصویر صفحه بخش
    #[Locked]
    public bool $contentOnly = false;

    // تصویر صفحه بخش در سایت (جدا از ایکون)
    public $bannerUpload;
    public ?string $bannerUrl = null;

    public function updatedBannerUpload()
    {
        $this->validate(['bannerUpload' => 'image|max:4096'], [], ['bannerUpload' => 'تصویر صفحه بخش']);
        $path = $this->bannerUpload->store(tenant('id').'/temp/'.$this->filePath, ['disk' => 'tenant']);

        $this->bannerUrl = Storage::disk('tenant')->url($path);
        $this->form['banner'] = $path;
    }

    public function deleteBanner()
    {
        // فایل موقت آپلود نشده حذف میشود؛ تصویر ذخیره شده با ذخیره فرم کنار گذاشته میشود
        if (str_contains((string) ($this->form['banner'] ?? ''), '/temp/')) {
            Storage::disk('tenant')->delete($this->form['banner']);
        }

        $this->bannerUpload = null;
        $this->bannerUrl = null;
        $this->form['banner'] = null;
    }

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
            'form.description' => 'nullable|string|max:200000',
        ];
    }

    public function createOrUpdateSection()
    {
        // دسترسی از روی سرور دوباره سنجیده میشود، نه از روی contentOnly
        if ($this->isEdited && Gate::denies('update', $this->service)) {
            return $this->saveContentOnly();
        }
        $this->authorize($this->isEdited ? 'update' : 'create', $this->isEdited ? $this->service : Service::class);

        $this->validate();
        if (! app()->environment('local')) {
            Cache::forget('most_viewed_service');
        }
        //data for update Or create Service
        $parentId = null;
        if (isset($this->form['parent'])) {
            $parentId = $this->form['parent'] == 0 || null ? null : $this->form['parent'];
        }
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
        $modelCreateOrUpdate['detail'][Service::DESCRIPTION] = Service::sanitizeDescription($this->form['description'] ?? null);

        $modelCreateOrUpdate['detail'][Service::BANNER] = $this->storeBanner();
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

    /** ذخیره فقط توضیحات و تصویر؛ بقیه detail دست نخورده میماند */
    private function saveContentOnly()
    {
        $this->authorize('editContent', $this->service);
        $this->validate(['form.description' => 'nullable|string|max:200000']);

        $detail = $this->service->detail ?? [];
        $detail[Service::DESCRIPTION] = Service::sanitizeDescription($this->form['description'] ?? null);
        $detail[Service::BANNER] = $this->storeBanner();
        $this->service->update(['detail' => $detail]);

        return redirect()->route('admin.service.list')->with('success', 'توضیحات و تصویر بخش ذخیره شد');
    }

    /** انتقال تصویر تازه آپلود شده از پوشه موقت؛ مسیر نهایی یا null */
    private function storeBanner(): ?string
    {
        $banner = $this->form['banner'] ?? null;
        if ($banner && str_contains($banner, '/temp/')) {
            $finalBanner = str_replace('temp/', '', $banner);
            Storage::disk('tenant')->move($banner, $finalBanner);
            $banner = $finalBanner;
        }

        return $banner ?: null;
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
        $this->form['description'] = $this->service->descriptionHtml();
        $this->form['banner'] = $this->service->bannerPath();
        $this->bannerUrl = $this->form['banner'] ? Storage::disk('tenant')->url($this->form['banner']) : null;
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
        $this->fetchdata['services'] = Service::whereNull('parent_id')->get();
        if ($service instanceof Service) {
            $this->service           =  $service;
            $this->isEdited          = true;
            $this->contentOnly       = Gate::denies('update', $service);
            $this->addInitialValues();

            //  load  image
            $this->photo = $service->icon;
            $path = $service->icon;
            if ($path) {
                $this->form['photo'] = $path ; 
                $this->uploadedPhotoUrl = Storage::disk('tenant')->url($path);
                $this->uploadedFileName = basename($path);
                $this->uploadedFileType = getFileIconClass(pathinfo($path, PATHINFO_EXTENSION));

            } else {
                $this->uploadedPhotoUrl = null;
            }
            $this->fetchdata['services'] = $this->fetchdata['services']->filter(function ($item) use ($service) {
                return $item->id !== $service->id; // Compare the IDs of the models
            });
        } else {
            $this->form['priority']      = Service::maxPriority();
            $this->form['active']        = 'true';
        }



        $this->fetchdata['doctors']  = User::doctors();
        $this->fetchdata['places']   = Place::active()->get();
    }
    public function render()
    {
        return view('service::livewire.create-or-update');
    }
}
