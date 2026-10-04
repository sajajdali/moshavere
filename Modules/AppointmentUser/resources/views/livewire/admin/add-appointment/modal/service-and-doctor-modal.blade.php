<div class="sdc-root" dir="rtl">
    <div class="modal fade" id="changeDocmodal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        wire:ignore.self aria-labelledby="changeDoctorModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <header class="sdc-head">
                    <div class="sdc-head-top">
                        <div class="sdc-heading">
                            <strong id="changeDoctorModalTitle">
                                @if ($step === 1) انتخاب پزشک @elseif($step === 2) انتخاب بخش @elseif($step === 3) انتخاب مطب @else انتخاب زیربخش @endif
                            </strong>
                            <span>
                                @if ($step === 1)
                                    پزشک مورد نظر را برای مشاهده بخش‌ها انتخاب کنید.
                                @elseif($step === 2)
                                    بخش مورد نظر برای {{ $docSection?->fullName }} را انتخاب کنید.
                                @elseif($step === 3)
                                    محل مراجعه را برای نوبت جدید مشخص کنید.
                                @else
                                    زیربخش مورد نظر را برای محاسبه زمان نوبت انتخاب کنید.
                                @endif
                            </span>
                        </div>
                        <button type="button" class="sdc-close" wire:click="closeModal" data-bs-dismiss="modal"
                            aria-label="بستن">×</button>
                    </div>
                    <div class="sdc-steps {{ $step === 4 ? 'has-segments' : '' }}" aria-label="مراحل تغییر پزشک و بخش">
                        <span class="sdc-step {{ $step >= 1 ? 'active' : '' }}">۱</span><span class="sdc-step-label {{ $step === 1 ? 'active' : '' }}">پزشک</span>
                        <span class="sdc-step-line"></span>
                        <span class="sdc-step {{ $step >= 2 ? 'active' : '' }}">۲</span><span class="sdc-step-label {{ $step === 2 ? 'active' : '' }}">بخش</span>
                        <span class="sdc-step-line"></span>
                        <span class="sdc-step {{ $step >= 3 ? 'active' : '' }}">۳</span><span class="sdc-step-label {{ $step === 3 ? 'active' : '' }}">مطب</span>
                        @if ($step === 4)
                            <span class="sdc-step-line"></span>
                            <span class="sdc-step active">۴</span><span class="sdc-step-label active">زیربخش</span>
                        @endif
                    </div>
                </header>

                @if ($step === 1 || $step === 2)
                    <div class="sdc-search">
                        <div class="sdc-search-box">
                            <i class="fa fa-search" aria-hidden="true"></i>
                            @if ($step === 1)
                                <input type="search" wire:model="search.doctor" wire:keydown.enter="searchDocAndSection" placeholder="جست‌وجوی نام پزشک" aria-label="جست‌وجوی پزشک">
                            @else
                                <input type="search" wire:model="search.service" wire:keydown.enter="searchDocAndSection" placeholder="جست‌وجوی نام بخش" aria-label="جست‌وجوی بخش">
                            @endif
                            <button type="button" wire:click="searchDocAndSection" wire:loading.attr="disabled" wire:target="searchDocAndSection">جست‌وجو</button>
                        </div>
                        @if (!empty($search['doctor']) || !empty($search['service']))
                            <button type="button" class="sdc-clear" wire:click="ignoreSearch">پاک کردن جست‌وجو</button>
                        @endif
                    </div>
                @endif

                <main class="sdc-body">
                    <div class="sdc-loading" wire:loading.flex wire:target="doctorSelected,selectSection,selectplace,selectSegment,searchDocAndSection,ignoreSearch,previousStep">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span>در حال دریافت اطلاعات…</span>
                    </div>

                    @error('selectDoctor')<div class="sdc-notice sdc-error" role="alert">{{ $message }}</div>@enderror
                    @error('selectService')<div class="sdc-notice sdc-error" role="alert">{{ $message }}</div>@enderror
                    @error('selectPlace')<div class="sdc-notice sdc-error" role="alert">{{ $message }}</div>@enderror
                    @error('selectSegment')<div class="sdc-notice sdc-error" role="alert">{{ $message }}</div>@enderror

                    @if ($step === 1)
                        <div class="sdc-options" wire:loading.remove wire:target="doctorSelected,searchDocAndSection">
                            @forelse ($doctors as $doctor)
                                @php
                                    // read the name from the preloaded metas; the full_name accessor runs two queries per doctor
                                    $doctorName = trim(trim((string) $doctor->metas->where('meta_key', \Modules\User\Enum\UserMetaEnum::FIRST_NAME)->last()?->meta_value) . ' ' . trim((string) $doctor->metas->where('meta_key', \Modules\User\Enum\UserMetaEnum::LAST_NAME)->last()?->meta_value));
                                @endphp
                                <button type="button" class="sdc-option" wire:click="doctorSelected({{ $doctor->id }})">
                                    <span class="sdc-option-icon">{{ mb_substr($doctorName, 0, 1) }}</span>
                                    <span class="sdc-option-copy"><strong>{{ $doctorName }}</strong><small>مشاهده بخش‌های پزشک</small></span>
                                    <i class="fa fa-angle-left sdc-option-arrow" aria-hidden="true"></i>
                                </button>
                            @empty
                                <div class="sdc-empty"><strong>پزشکی یافت نشد</strong><span>عبارت دیگری را جست‌وجو کنید.</span></div>
                            @endforelse
                        </div>
                    @elseif($step === 2)
                        <div class="sdc-selected"><span class="sdc-selected-icon"><i class="fa fa-user-md" aria-hidden="true"></i></span><span><small>پزشک انتخاب‌شده</small><strong>{{ $docSection?->fullName }}</strong></span></div>
                        <div class="sdc-options">
                            @forelse ($fetchData['service'] ?? [] as $service)
                                @php $parentService = $service->parent_id ? ($fetchData['service'] ?? collect())->firstWhere('id', $service->parent_id) : null; @endphp
                                <label class="sdc-option sdc-radio-option {{ $service->parent_id ? 'is-child' : '' }}" for="service-option-{{ $service->id }}">
                                    <input type="radio" name="appointment-service" value="{{ $service->id }}" wire:model="form.service" id="service-option-{{ $service->id }}">
                                    <span class="sdc-radio-mark"></span>
                                    <span class="sdc-option-copy"><strong>{{ $service->title }}</strong>@if ($parentService)<small>زیرمجموعه {{ $parentService->title }}</small>@else<small>بخش اصلی</small>@endif</span>
                                </label>
                            @empty
                                <div class="sdc-empty"><strong>بخشی یافت نشد</strong><span>این پزشک بخش قابل انتخابی ندارد یا نتیجه‌ای برای جست‌وجو پیدا نشد.</span></div>
                            @endforelse
                        </div>
                    @elseif($step === 3)
                        <div class="sdc-selection-summary">
                            <span><small>پزشک</small><strong>{{ $docSection?->fullName }}</strong></span>
                            <i class="fa fa-angle-left" aria-hidden="true"></i>
                            <span><small>بخش</small><strong>{{ optional(($fetchData['service'] ?? collect())->firstWhere('id', $form['service_id'] ?? null))->title }}</strong></span>
                        </div>
                        <div class="sdc-options">
                            @forelse ($fetchData['places'] ?? [] as $place)
                                <button type="button" class="sdc-option" wire:click="selectplace({{ $place->id }})">
                                    <span class="sdc-option-icon"><i class="fa fa-map-marker" aria-hidden="true"></i></span>
                                    <span class="sdc-option-copy"><strong>{{ $place->title }}</strong><small>انتخاب این محل مراجعه</small></span>
                                    <i class="fa fa-angle-left sdc-option-arrow" aria-hidden="true"></i>
                                </button>
                            @empty
                                <div class="sdc-empty"><strong>مطب فعالی یافت نشد</strong><span>تنظیمات حضور پزشک و بخش انتخاب‌شده را بررسی کنید.</span></div>
                            @endforelse
                        </div>
                    @elseif($step === 4)
                        <div class="sdc-selection-summary">
                            <span><small>پزشک</small><strong>{{ $docSection?->fullName }}</strong></span>
                            <i class="fa fa-angle-left" aria-hidden="true"></i>
                            <span><small>بخش</small><strong>{{ optional(($fetchData['service'] ?? collect())->firstWhere('id', $form['service_id'] ?? null))->title }}</strong></span>
                        </div>
                        @if ($fetchData['segmentSingleChoice'] ?? false)
                            <div class="sdc-options">
                                @forelse ($fetchData['segmentItems'] ?? [] as $segmentItem)
                                    <button type="button" class="sdc-option" wire:click="selectSegment({{ $segmentItem->id }})">
                                        <span class="sdc-option-icon"><i class="fa fa-list-ul" aria-hidden="true"></i></span>
                                        <span class="sdc-option-copy"><strong>{{ $segmentItem->title }}</strong><small>@if ($segmentItem->time) مدت نوبت: {{ $segmentItem->time }} دقیقه @else انتخاب این زیربخش @endif</small></span>
                                        <i class="fa fa-angle-left sdc-option-arrow" aria-hidden="true"></i>
                                    </button>
                                @empty
                                    <div class="sdc-empty"><strong>زیربخشی تعریف نشده است</strong><span>تنظیمات بخش‌بندی این حضور را بررسی کنید.</span></div>
                                @endforelse
                            </div>
                        @else
                            <div class="sdc-notice">می‌توانید یک یا چند زیربخش را انتخاب کنید.</div>
                            <div class="sdc-options">
                                @forelse ($fetchData['segmentItems'] ?? [] as $segmentItem)
                                    <label class="sdc-option sdc-check-option" for="segment-option-{{ $segmentItem->id }}">
                                        <input type="checkbox" wire:model="form.segments.{{ $segmentItem->id }}" id="segment-option-{{ $segmentItem->id }}">
                                        <span class="sdc-check-mark"><i class="fa fa-check" aria-hidden="true"></i></span>
                                        <span class="sdc-option-copy"><strong>{{ $segmentItem->title }}</strong><small>@if ($segmentItem->time) مدت: {{ $segmentItem->time }} دقیقه @else قابل انتخاب @endif</small></span>
                                    </label>
                                @empty
                                    <div class="sdc-empty"><strong>زیربخشی تعریف نشده است</strong><span>تنظیمات بخش‌بندی این حضور را بررسی کنید.</span></div>
                                @endforelse
                            </div>
                        @endif
                    @endif
                </main>

                <footer class="sdc-foot">
                    @if ($step > 1)
                        <button type="button" class="sdc-button sdc-secondary" wire:click="previousStep" wire:loading.attr="disabled" wire:target="previousStep"><i class="fa fa-angle-right" aria-hidden="true"></i><span>مرحله قبل</span></button>
                    @else
                        <span></span>
                    @endif
                    <div class="sdc-foot-actions">
                        @if ($step === 2)
                            <button type="button" class="sdc-button sdc-primary" wire:click="selectSection" wire:loading.attr="disabled" wire:target="selectSection">ادامه</button>
                        @endif
                        @if ($step === 4 && !($fetchData['segmentSingleChoice'] ?? false))
                            <button type="button" class="sdc-button sdc-primary" wire:click="selectSegment" wire:loading.attr="disabled" wire:target="selectSegment">ادامه و نمایش زمان‌ها</button>
                        @endif
                        <button type="button" class="sdc-button sdc-secondary" wire:click="closeModal" data-bs-dismiss="modal">بی‌خیال</button>
                    </div>
                </footer>
            </div>
        </div>
    </div>
</div>
