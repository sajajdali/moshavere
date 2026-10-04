<div>
    <div class="modal ac-modal effect-flip-horizontal fade" id="segmentModal" tabindex="-1"
        aria-labelledby="segmentModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered" wire:key='{{ time() }}'>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="segmentModalLabel">
                        <span class="ac-modal-icon"><i class="fa-solid fa-list-ul" aria-hidden="true"></i></span>
                        <span>
                            انتخاب بخش‌بندی
                            <span class="ac-modal-sub">زیربخش مورد نظر را مشخص کنید</span>
                        </span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if (isset($fetchData['segments']['items']) && $fetchData['segments']['items']->isNotEmpty())
                        @if ($fetchData['segments']['is_one_choice'])
                            <span class="ac-modal-hint">بخش‌بندی مورد نظر را انتخاب کنید</span>
                            <div class="ac-option-list">
                                @foreach ($fetchData['segments']['items'] as $segment)
                                    <button type="button" class="ac-option" wire:key='segment-{{ $segment->id }}'
                                        wire:click='segmentSelected({{ $segment->id }})'>
                                        <span class="ac-option-text">
                                            <span class="ac-option-title">{{ $segment->title }}</span>
                                        </span>
                                        <span class="ac-option-mark"></span>
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <span class="ac-modal-hint">لطفا بخش‌بندی‌های مورد نظر را انتخاب کنید (چند انتخابی)</span>
                            <div class="ac-option-list">
                                @foreach ($fetchData['segments']['items'] as $segment)
                                    <label class="ac-check" for="{{ $loop->index }}-segment-items"
                                        wire:key='segment-check-{{ $segment->id }}'>
                                        <input type="checkbox" id="{{ $loop->index }}-segment-items"
                                            wire:model='form.segmentSelectedIem.{{ $segment->id }}'>
                                        <span>{{ $segment->title }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="ac-note">
                            <strong>زیربخش‌ها تعریف نشده‌اند</strong>
                            ابتدا از تنظیمات نوبت‌دهی، بخش‌بندی مورد نظر را تعریف کنید.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    @if (isset($fetchData['segments']['items']) &&
                            $fetchData['segments']['items']->isNotEmpty() &&
                            !$fetchData['segments']['is_one_choice'])
                        <button type="button" class="ac-btn-primary" wire:click='segmentSelected'>ادامه و انتخاب زمان</button>
                    @endif
                    <button type="button" class="ac-btn-ghost" data-bs-dismiss="modal">بی‌خیال</button>
                </div>
            </div>
        </div>
    </div>
</div>
