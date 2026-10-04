<div>
    <div class="modal ac-modal effect-slide-in-bottom fade" id="docModal" tabindex="-1" aria-labelledby="docModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="docModalLabel">
                        <span class="ac-modal-icon"><i class="fa-solid fa-user-doctor" aria-hidden="true"></i></span>
                        <span>
                            انتخاب پزشک
                            <span class="ac-modal-sub">پزشک مورد نظر را انتخاب کنید</span>
                        </span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if (isset($fetchData['docList']) && $fetchData['docList']->isNotEmpty())
                        <span class="ac-modal-hint">پزشک مورد نظر را انتخاب کنید</span>
                        <div class="ac-option-list">
                            @foreach ($fetchData['docList'] as $doc)
                                <button type="button" class="ac-option" data-bs-dismiss="modal"
                                    wire:key="modal-doc-{{ $doc->id }}"
                                    wire:click="docSelectedFromModal({{ $doc->id }})">
                                    <span class="ac-option-text">
                                        <span class="ac-option-title">{{ $doc->fullName }}</span>
                                        <span class="ac-option-meta">{{ filled($doc->DocSpecialities()) ? $doc->DocSpecialities() : 'تخصص ثبت نشده' }}</span>
                                    </span>
                                    <span class="ac-option-mark"></span>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="ac-note">
                            <strong>هیچ پزشکی برای این بخش تعریف نشده است</strong>
                            لطفا ابتدا برای این بخش پزشک انتخاب کنید و تنظیمات را انجام دهید تا بتوانید اقدام به ثبت نوبت نمایید.
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
