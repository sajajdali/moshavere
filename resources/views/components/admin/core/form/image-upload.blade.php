@props([
    'label' => 'Upload File',   // The label for the input
    'uploadedPhotoUrl' => null, // The current uploaded photo URL (for images)
    'uploadedFileName' => null, // The name of the uploaded file (for non-images)
    'uploadedFileType' => null, // The type of the uploaded file (e.g., image, pdf, etc.)
    'deleteAction' => null,     // The method to delete the uploaded file
    'model' => '',              // The wire:model binding for the file
    'id' => 'file',             // The ID for the input field
])
<label class="form-label" for="{{ $id }}">{{ $label }}</label>
<div class="d-flex align-items-center">
    @if ($uploadedPhotoUrl)
        @if (str_contains($uploadedFileType, 'image'))
            <!-- نمایش تصویر -->
            <div class="me-3">
                <img src="{{ $uploadedPhotoUrl }}" alt="Uploaded Image" class="img-thumbnail"
                     style="width: 100px; height: 100px; object-fit: cover;">
            </div>
        @else
            <!-- نمایش آیکون و نام فایل -->
            <div class="me-3 d-flex align-items-center mb-3">
                <i class="fa {{ getFileIconClass($uploadedFileType) }} fa-2x text-primary me-2"></i>
                <p class="mb-0 text-truncate" style="max-width: 200px;">{{ $uploadedFileName }}</p>
            </div>
        @endif
        <button type="button"
                wire:click="{{ $deleteAction }}"
                class="btn btn-danger btn-sm d-flex align-items-center">
            <span wire:loading wire:target="{{ $deleteAction }}"
                  class="spinner-border spinner-border-sm me-2" role="status"></span>
            <span wire:loading.remove wire:target="{{ $deleteAction }}">Delete</span>
            <span wire:loading wire:target="{{ $deleteAction }}">Deleting...</span>
        </button>
    @endif
</div>
<div class="input-group">
    <input wire:model="{{ $model }}" type="file" class="form-control" id="{{ $id }}"
           wire:loading.attr="disabled" wire:target="{{ $model }}">
    <div class="mt-2 ">
        <div wire:loading wire:target="{{ $model }}">
            <div class="d-flex align-items-center gap-2 mr-2 text-secondary small" style="margin-top: 6px;">
                <div class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></div>
                <span>در حال آپلود تصویر...</span>
            </div>
        </div>
    </div>
</div>
@error($model)
<div class="text-danger mt-2">{{ $message }}</div>
@enderror
