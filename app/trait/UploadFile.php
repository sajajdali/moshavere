<?php

namespace App\trait;

use Illuminate\Support\Facades\Storage;

trait UploadFile
{
    public $photo;
    public $uploadedPhotoUrl;
    public $uploadedFileName;
    public $uploadedFileType;
    public function updatedPhoto()
    {
        $this->validate([
            'photo' => 'file',
        ]);
        $path = $this->photo->store(tenant('id').'/temp/'.$this->filePath, ['disk' => 'tenant']);

        $this->uploadedPhotoUrl = Storage::disk('tenant')->url($path);
        $this->uploadedFileName = $this->photo->getClientOriginalName();
        $this->uploadedFileType = $this->photo->getMimeType();

        $this->form['photo'] = $path;
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

    }
}
