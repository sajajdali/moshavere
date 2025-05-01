<?php


namespace Modules\Setting\Livewire\Admin\Setting\Component;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Setting\Enum\SettingKeyEnum;

class Image extends Component
{
    use WithFileUploads;

    public $photo;
    public $uploadedPhotoUrl;
    public $uploadedFileName;
    public $uploadedFileType;

    public mixed $old_value;
    public SettingKeyEnum $meta;

    protected $filePath = 'setting';

    public function mount()
    {
        if ($this->old_value !== null) {
            $this->uploadedPhotoUrl = Storage::disk('tenant')->url($this->old_value);
            $this->uploadedFileName = basename($this->old_value);
            $this->uploadedFileType = Storage::disk('tenant')->mimeType($this->old_value) ?? 'image/jpeg';
        }
    }

    public function updatedPhoto()
    {
        $this->validate([
            'photo' => 'image|max:2048', // محدودیت ۲ مگابایت برای تصاویر
        ]);

        $path = $this->photo->store(tenant('id') . '/' . $this->filePath, 'tenant');

        $this->uploadedPhotoUrl = Storage::disk('tenant')->url($path);
        $this->uploadedFileName = $this->photo->getClientOriginalName();
        $this->uploadedFileType = $this->photo->getMimeType();

        $this->dispatch('settingUpdateListener', settingKey: $this->meta->value, value: $path);
    }

    public function deleteFile()
    {
        if ($this->uploadedPhotoUrl) {
            $relativePath = str_replace(Storage::disk('tenant')->url(''), '', $this->uploadedPhotoUrl);
            Storage::disk('tenant')->delete($relativePath);
        }

        $this->photo = null;
        $this->uploadedPhotoUrl = null;
        $this->uploadedFileName = null;
        $this->uploadedFileType = null;

        $this->dispatch('settingUpdateListener', settingKey: $this->meta->value, value: null);
    }

    public function render()
    {
        return view('setting::livewire.admin.setting.component.image');
    }
}
