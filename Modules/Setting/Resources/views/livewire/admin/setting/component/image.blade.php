<div class="form-group mb-4">
    <x-admin.core.form.image-upload
        label="{{ $meta->getName() }}"
        uploadedPhotoUrl="{{ $uploadedPhotoUrl }}"
        uploadedFileName="{{ $uploadedFileName }}"
        uploadedFileType="{{ $uploadedFileType }}"
        deleteAction="deleteFile"
        model="photo"
        id="setting_file_{{ $meta->value }}"
    />

    @if ($meta->getDescription())
        <small class="text-muted d-block mt-2">{!! $meta->getDescription() !!}</small>
    @endif
</div>
