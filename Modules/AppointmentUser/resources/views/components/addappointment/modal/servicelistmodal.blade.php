<div>
    <div class="modal ac-modal effect-slide-in-bottom fade" id="serviceModal" tabindex="-1"
        aria-labelledby="serviceModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered" wire:key='{{ time() }}'>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="serviceModalLabel">
                        <span class="ac-modal-icon"><i class="fa-solid fa-stethoscope" aria-hidden="true"></i></span>
                        <span>
                            انتخاب بخش
                            <span class="ac-modal-sub">بخش مورد نظر را انتخاب کنید</span>
                        </span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if (isset($fetchData['ServiceList']) && $fetchData['ServiceList']->isNotEmpty())
                        <span class="ac-modal-hint">بخش مورد نظر را انتخاب کنید</span>
                        <div class="ac-option-list">
                            @foreach ($fetchData['ServiceList'] as $service)
                                <button type="button" class="ac-option" wire:key='modal-service-{{ $service->id }}'
                                    wire:click='serviceSelected({{ $service->id }})'>
                                    <span class="ac-option-main">
                                        @if ($service->icon)
                                            <span class="ac-option-thumb">
                                                <img src="{{ assetStorage($service->icon) }}" alt="">
                                            </span>
                                        @endif
                                        <span class="ac-option-text">
                                            <span class="ac-option-title">{{ $service->title }}</span>
                                        </span>
                                    </span>
                                    <span class="ac-option-mark"></span>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="ac-note">
                            <strong>بخشی برای این پزشک تعریف نشده است</strong>
                            ابتدا از تنظیمات نوبت‌دهی، بخش‌بندی و زمان حضور را مشخص کنید.
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
