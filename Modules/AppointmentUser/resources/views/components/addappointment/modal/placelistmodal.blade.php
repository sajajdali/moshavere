<div>
    <div class="modal ac-modal effect-slide-in-bottom fade" id="placeModal" tabindex="-1"
        aria-labelledby="placeModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="placeModalLabel">
                        <span class="ac-modal-icon"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span>
                        <span>
                            انتخاب مطب
                            <span class="ac-modal-sub">محل حضور را انتخاب کنید</span>
                        </span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if (isset($fetchData['placeList']) && $fetchData['placeList']->isNotEmpty())
                        <span class="ac-modal-hint">محل حضور مورد نظر را انتخاب کنید</span>
                        <div class="ac-option-list">
                            @foreach ($fetchData['placeList'] as $place)
                                <button type="button" class="ac-option" data-bs-dismiss="modal"
                                    wire:key="modal-place-{{ $place->id }}"
                                    wire:click="placeSelected({{ $place->id }})">
                                    <span class="ac-option-text">
                                        <span class="ac-option-title">{{ $place->title }}</span>
                                    </span>
                                    <span class="ac-option-mark"></span>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="ac-note">
                            <strong>هیچ مطبی تعریف نشده است</strong>
                            لطفا ابتدا یک مطب به سیستم اضافه کرده و تنظیمات زمان‌های حضور را از قسمت تنظیمات نوبت‌دهی انجام دهید.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="ac-btn-ghost" data-bs-dismiss="modal">بی‌خیال</button>
                </div>
            </div>
        </div>
    </div>
</div>
