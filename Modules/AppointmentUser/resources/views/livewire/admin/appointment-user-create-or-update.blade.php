@php
    $startOnSections = $canSeeSections && filled($search['searchService'] ?? null);
@endphp

<div class="appointment-create-page" dir="rtl" x-data="{ pane: '{{ $startOnSections ? 'sections' : 'doctors' }}' }">
    <div class="ac-shell">
        @includeIf('onlineconsultation::appointment-entry')
        @include('admin::layouts.components.alert')

        <div class="ac-topbar">
            <div>
                <div class="ac-breadcrumb">
                    <span>نوبت‌دهی</span><span class="sep">/</span><span class="cur">افزودن نوبت</span>
                </div>
                <h1 class="ac-title">افزودن نوبت</h1>
                <p class="ac-subtitle">پزشک یا بخش مورد نظر را انتخاب کنید؛ سپس در پنجره‌ی بعدی بخش و محل حضور را مشخص می‌کنید.</p>
            </div>
            <div class="ac-userchip">
                <span class="dot"></span>
                <span>{{ auth()->user()->fullName ?? '--' }}</span>
            </div>
        </div>

        <div class="ac-toolbar">
            <div class="ac-tabs" role="tablist">
                <button type="button" role="tab" class="ac-tab" :class="{ active: pane === 'doctors' }"
                    :aria-selected="pane === 'doctors'" @click="pane = 'doctors'">پزشک‌ها</button>
                @if ($canSeeSections)
                    <button type="button" role="tab" class="ac-tab" :class="{ active: pane === 'sections' }"
                        :aria-selected="pane === 'sections'" @click="pane = 'sections'">بخش‌ها</button>
                @endif
            </div>

            <div class="ac-search-wrap">
                <div class="ac-search" x-show="pane === 'doctors'" @if ($startOnSections) style="display:none" @endif>
                    <input type="search" wire:model.live.debounce.400ms='search.doctors' placeholder="نام یا نام خانوادگی پزشک…"
                        aria-label="جست‌وجوی پزشک">
                    <i class="fa-solid fa-magnifying-glass ac-search-icon" aria-hidden="true"></i>
                </div>
                @if ($canSeeSections)
                    <div class="ac-search" x-show="pane === 'sections'" @if (! $startOnSections) style="display:none" @endif>
                        <input type="search" wire:model.live.debounce.400ms='search.searchService' placeholder="نام بخش…"
                            aria-label="جست‌وجوی بخش">
                        <i class="fa-solid fa-magnifying-glass ac-search-icon" aria-hidden="true"></i>
                    </div>
                @endif
                @if (filled($search['doctors'] ?? null) || filled($search['searchService'] ?? null))
                    <button type="button" class="ac-reset" wire:click='ignoreSearch'>نمایش همه</button>
                @endif
                <span class="ac-count" x-show="pane === 'doctors'" @if ($startOnSections) style="display:none" @endif>{{ number_format($doctors?->total() ?? 0) }} پزشک</span>
                @if ($canSeeSections)
                    <span class="ac-count" x-show="pane === 'sections'" @if (! $startOnSections) style="display:none" @endif>{{ number_format($Services->total()) }} بخش</span>
                @endif
            </div>
        </div>

        {{-- doctor panel --}}
        <div x-show="pane === 'doctors'" @if ($startOnSections) style="display:none" @endif>
            @if (isset($doctors) && $doctors->isNotEmpty())
                <div class="ac-grid">
                    @foreach ($doctors as $doctor)
                        @php
                            // names and avatar come from the eager-loaded metas; the fullName accessor queries per doctor
                            $firstName = trim((string) $doctor->getMetas(\Modules\User\Enum\UserMetaEnum::FIRST_NAME)->last()?->meta_value);
                            $lastName = trim((string) $doctor->getMetas(\Modules\User\Enum\UserMetaEnum::LAST_NAME)->last()?->meta_value);
                            $fullName = trim("{$firstName} {$lastName}") ?: '--';
                            $hasAvatar = filled($doctor->getMetas(\Modules\User\Enum\UserMetaEnum::AVATAR)->sortByDesc('created_at')->first()?->meta_value);
                            $specTitles = $doctor->specialities->pluck('title')->filter()->implode('، ');
                            $serviceCount = $doctor->services->where('active', \App\Enum\ActiveEnum::ACTIVE)->count();
                        @endphp
                        <div class="ac-card" role="button" tabindex="0" wire:key='doctor-{{ $doctor->id }}'
                            wire:click='docSelected({{ $doctor->id }})' wire:target='docSelected({{ $doctor->id }})'
                            wire:loading.class='is-loading'>
                            <div class="ac-card-head">
                                @if ($hasAvatar)
                                    <div class="ac-avatar ac-avatar-img"><img src="{{ $doctor->getUserAvatar() }}" alt="" loading="lazy"></div>
                                @else
                                    <div class="ac-avatar">{{ mb_substr($fullName, 0, 1) }}</div>
                                @endif
                                <div class="ac-card-id">
                                    <span class="ac-name">{{ $fullName }}</span>
                                    <span class="ac-spec @if (blank($specTitles)) empty @endif">{{ filled($specTitles) ? $specTitles : 'تخصص ثبت نشده' }}</span>
                                </div>
                            </div>
                            <div class="ac-stats">
                                <div class="ac-stat">
                                    <span class="ac-stat-label">بخش فعال</span>
                                    <span class="ac-stat-value @if ($serviceCount === 0) warn @endif">{{ $serviceCount }}</span>
                                </div>
                                <div class="ac-stat">
                                    <span class="ac-stat-label">محل حضور</span>
                                    <span class="ac-stat-value">{{ $doctor->places->count() }}</span>
                                </div>
                            </div>
                            <div class="ac-card-cta">
                                <span wire:loading.remove wire:target='docSelected({{ $doctor->id }})'>افزودن نوبت</span>
                                <span wire:loading wire:target='docSelected({{ $doctor->id }})'><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> در حال بارگذاری…</span>
                                <i class="fa-solid fa-chevron-left chev" aria-hidden="true"></i>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="ac-pagination mt-3">{{ $doctors->links() }}</div>
            @else
                <div class="ac-empty">
                    <i class="fa-solid fa-user-doctor" aria-hidden="true"></i>
                    <strong>پزشکی یافت نشد</strong>
                    <span>عبارت جست‌وجو را تغییر دهید یا ابتدا پزشکان را به سیستم اضافه کنید.</span>
                </div>
            @endif
        </div>

        {{-- section panel --}}
        @if ($canSeeSections)
            <div x-show="pane === 'sections'" @if (! $startOnSections) style="display:none" @endif>
                @if ($Services->isNotEmpty())
                    <div class="ac-grid">
                        @foreach ($Services as $service)
                            <div class="ac-card" role="button" tabindex="0" wire:key='service-{{ $service->id }}'
                                wire:click='serviceSelectedFromServiceSection({{ $service->id }})'
                                wire:target='serviceSelectedFromServiceSection({{ $service->id }})' wire:loading.class='is-loading'>
                                <div class="ac-card-head">
                                    @if ($service->icon)
                                        <div class="ac-avatar ac-avatar-img"><img src="{{ assetStorage($service->icon) }}" alt="" loading="lazy"></div>
                                    @else
                                        <div class="ac-avatar ac-avatar-plain"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></div>
                                    @endif
                                    <div class="ac-card-id">
                                        <span class="ac-name">{{ $service->title }}</span>
                                        <span class="ac-option-meta">{{ number_format($service->user_count) }} پزشک ارائه‌دهنده</span>
                                    </div>
                                </div>
                                <div class="ac-card-cta">
                                    <span wire:loading.remove wire:target='serviceSelectedFromServiceSection({{ $service->id }})'>افزودن نوبت</span>
                                    <span wire:loading wire:target='serviceSelectedFromServiceSection({{ $service->id }})'><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> در حال بارگذاری…</span>
                                    <i class="fa-solid fa-chevron-left chev" aria-hidden="true"></i>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="ac-pagination mt-3">{{ $Services->links() }}</div>
                @else
                    <div class="ac-empty">
                        <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                        <strong>بخشی یافت نشد</strong>
                        <span>عبارت جست‌وجو را تغییر دهید یا فهرست کامل را ببینید.</span>
                    </div>
                @endif
            </div>
        @endif
    </div>

    @include('appointmentuser::components.addappointment.modal.doclistmodal')
    @include('appointmentuser::components.addappointment.modal.placelistmodal')
    @include('appointmentuser::components.addappointment.modal.servicelistmodal')
    @include('appointmentuser::components.addappointment.modal.segmentListModal')
</div>

@push('styles')
    <style>
        .appointment-create-page { --ac-primary: var(--primary-bg-color, #0070bb); --ac-ink: #14171c; --ac-border: #e6e8ec; font-size: 14px; padding-bottom: 32px; color: var(--ac-ink); }
        .ac-shell { display: flex; flex-direction: column; gap: 20px; }
        .ac-topbar { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; flex-wrap: wrap; margin-top: 20px; }
        .ac-breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: #8b9099; margin-bottom: 6px; }
        .ac-breadcrumb .sep { color: #c3c7ce; }
        .ac-breadcrumb .cur { color: var(--ac-ink); }
        .ac-title { margin: 0; font-size: 24px; font-weight: 700; }
        .ac-subtitle { margin: 6px 0 0; color: #6b7280; font-size: 13.5px; max-width: 60ch; }
        .ac-userchip { display: flex; align-items: center; gap: 8px; padding: 9px 14px; border-radius: 10px; background: #fff; border: 1px solid var(--ac-border); color: #4b5563; font-size: 13px; }
        .ac-userchip .dot { width: 7px; height: 7px; border-radius: 50%; background: #1a9e5b; }

        .ac-toolbar { background: #fff; border: 1px solid var(--ac-border); border-radius: 16px; padding: 14px; display: flex; flex-wrap: wrap; gap: 14px; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px rgba(16, 24, 40, .04); }
        .ac-tabs { display: flex; background: #f1f3f5; border-radius: 11px; padding: 4px; gap: 4px; }
        .ac-tab { padding: 9px 20px; border: none; border-radius: 8px; font-size: 13.5px; cursor: pointer; background: transparent; color: #6b7280; font-family: inherit; transition: all .15s; }
        .ac-tab:hover { color: var(--ac-ink); }
        .ac-tab.active { background: #fff; color: var(--ac-primary); font-weight: 600; box-shadow: 0 1px 3px rgba(20, 23, 28, .12); }
        .ac-search-wrap { display: flex; align-items: center; gap: 10px; flex: 1 1 320px; min-width: 0; justify-content: flex-end; }
        .ac-search { position: relative; flex: 0 1 360px; min-width: 0; }
        .ac-search input { width: 100%; padding: 11px 40px 11px 14px; border: 1px solid #e0e3e8; border-radius: 11px; background: #fbfbfc; font-size: 13.5px; color: var(--ac-ink); outline: none; font-family: inherit; }
        .ac-search input::-webkit-search-cancel-button { display: none; }
        .ac-search input:focus { border-color: var(--ac-primary); background: #fff; box-shadow: 0 0 0 3px color-mix(in srgb, var(--ac-primary) 12%, transparent); }
        .ac-search .ac-search-icon { position: absolute; inset-inline-start: 13px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #9aa0a8; font-size: 14px; }
        .ac-count { color: #8b9099; font-size: 12.5px; white-space: nowrap; }
        .ac-reset { padding: 10px 14px; border: 1px solid #d7dbe0; background: #fff; border-radius: 11px; font-size: 13px; color: #4b5563; cursor: pointer; font-family: inherit; white-space: nowrap; }
        .ac-reset:hover { background: #f6f7f9; color: var(--ac-ink); }

        .ac-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 340px), 1fr)); gap: 18px; }
        .ac-card { background: #fff; border: 1px solid var(--ac-border); border-radius: 16px; overflow: hidden; display: flex; flex-direction: column; cursor: pointer; transition: border-color .15s, box-shadow .15s; }
        .ac-card:hover, .ac-card:focus-visible { border-color: var(--ac-primary); box-shadow: 0 10px 24px -16px rgba(20, 23, 28, .3); outline: none; }
        .ac-card-head { padding: 20px; display: flex; gap: 16px; align-items: center; }
        .ac-avatar { width: 62px; height: 62px; flex: none; border-radius: 18px; background: color-mix(in srgb, var(--ac-primary) 10%, #fff); color: var(--ac-primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 22px; box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--ac-primary) 14%, transparent); }
        .ac-avatar-plain { background: #f1f2f4; color: #8b9099; box-shadow: inset 0 0 0 1px rgba(20, 23, 28, .07); }
        .ac-avatar-img { background: #f4f5f6; padding: 0; }
        .ac-avatar-img img { width: 100%; height: 100%; object-fit: cover; border-radius: inherit; }
        .ac-card-id { display: flex; flex-direction: column; gap: 7px; min-width: 0; }
        .ac-name { font-weight: 600; font-size: 17px; line-height: 1.35; color: var(--ac-ink); word-break: break-word; }
        .ac-spec { font-size: 12.5px; padding: 4px 12px; border-radius: 999px; align-self: flex-start; background: #f1f3f5; color: #4b5563; line-height: 1.6; }
        .ac-spec.empty { background: #fdecec; color: #a4322f; }
        .ac-stats { display: grid; grid-template-columns: 1fr 1fr; border-top: 1px solid #f0f1f4; background: #fbfcfc; }
        .ac-stat { padding: 12px 20px; display: flex; flex-direction: column; gap: 3px; }
        .ac-stat + .ac-stat { border-inline-start: 1px solid #f0f1f4; }
        .ac-stat-label { font-size: 11.5px; color: #9aa0a8; }
        .ac-stat-value { font-size: 16px; font-weight: 600; color: #374151; }
        .ac-stat-value.warn { color: #a4322f; }
        .ac-card-cta { margin-top: auto; padding: 14px; border-top: 1px solid #f0f1f4; background: #fff; color: var(--ac-primary); font-size: 14.5px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .ac-card-cta .chev { font-size: 12px; }
        .ac-card:hover .ac-card-cta { background: color-mix(in srgb, var(--ac-primary) 6%, #fff); }
        .ac-card.is-loading { opacity: .55; pointer-events: none; }

        .ac-empty { background: #fff; border: 1px dashed #d7dbe0; border-radius: 16px; padding: 48px 24px; text-align: center; color: #6b7280; display: flex; flex-direction: column; align-items: center; gap: 6px; }
        .ac-empty > i { font-size: 32px; opacity: .3; margin-bottom: 6px; }
        .ac-empty strong { font-weight: 600; color: var(--ac-ink); }
        .ac-empty span { font-size: 13px; }
        .ac-pagination { display: flex; justify-content: center; }
        .ac-pagination .pagination { margin-bottom: 0; }

        /* modals restyled to match */
        .ac-modal .modal-content { border: none; border-radius: 18px; overflow: hidden; box-shadow: 0 30px 70px -20px rgba(10, 12, 16, .5); }
        .ac-modal .modal-header { padding: 20px 22px; border-bottom: 1px solid #f0f1f4; align-items: center; gap: 14px; }
        .ac-modal .modal-title { display: flex; gap: 12px; align-items: center; font-size: 15.5px; font-weight: 600; color: var(--ac-ink, #14171c); }
        .ac-modal-icon { width: 42px; height: 42px; flex: none; border-radius: 50%; background: color-mix(in srgb, var(--primary-bg-color, #0070bb) 10%, #fff); color: var(--primary-bg-color, #0070bb); display: flex; align-items: center; justify-content: center; font-size: 16px; }
        .ac-modal-sub { display: block; color: #6b7280; font-size: 12.5px; font-weight: 400; margin-top: 3px; }
        .ac-modal .modal-body { padding: 18px 22px; overflow-y: auto !important; max-height: min(52vh, 420px); overscroll-behavior: contain; }
        .ac-modal-hint { display: block; font-size: 12.5px; color: #8b9099; margin-bottom: 10px; }
        .ac-option-list { display: flex; flex-direction: column; gap: 10px; }
        .ac-option { width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; border-radius: 12px; cursor: pointer; background: #fff; text-align: start; font-family: inherit; border: 1px solid #e6e8ec; transition: border-color .15s, box-shadow .15s; }
        .ac-option:hover { border-color: var(--primary-bg-color, #0070bb); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary-bg-color, #0070bb) 12%, transparent); }
        .ac-option-main { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .ac-option-thumb { width: 38px; height: 38px; flex: none; border-radius: 9px; overflow: hidden; background: #f4f5f6; }
        .ac-option-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .ac-option-text { display: flex; flex-direction: column; gap: 4px; align-items: flex-start; text-align: start; min-width: 0; }
        .ac-option-title { font-weight: 600; font-size: 14px; color: var(--ac-ink, #14171c); }
        .ac-option-meta { color: #6b7280; font-size: 12.5px; }
        .ac-option-mark { width: 18px; height: 18px; flex: none; border-radius: 50%; border: 1.5px solid #cfd4da; background: transparent; }
        .ac-option:hover .ac-option-mark { border-color: var(--primary-bg-color, #0070bb); }
        .ac-check { display: flex; align-items: center; gap: 10px; padding: 13px 16px; border: 1px solid #e6e8ec; border-radius: 12px; cursor: pointer; margin: 0; }
        .ac-check:hover { border-color: var(--primary-bg-color, #0070bb); }
        .ac-check input { width: 17px; height: 17px; accent-color: var(--primary-bg-color, #0070bb); cursor: pointer; margin: 0; }
        .ac-check span { font-size: 14px; color: var(--ac-ink, #14171c); }
        .ac-note { border: 1px solid #f2e3c4; background: #fdf8ec; border-radius: 12px; padding: 16px; color: #7a5c1e; font-size: 13px; line-height: 1.9; }
        .ac-note strong { display: block; margin-bottom: 4px; color: #5d4512; }
        .ac-modal .modal-footer { padding: 16px 22px; border-top: 1px solid #f0f1f4; background: #fbfbfc; display: flex; gap: 10px; justify-content: flex-start; }
        .ac-btn-primary { padding: 11px 18px; border: none; border-radius: 11px; font-size: 13.5px; font-weight: 500; color: #fff; background: var(--primary-bg-color, #0070bb); cursor: pointer; font-family: inherit; }
        .ac-btn-primary:hover { filter: brightness(.92); color: #fff; }
        .ac-btn-ghost { padding: 11px 16px; border: 1px solid #d7dbe0; background: #fff; border-radius: 11px; font-size: 13.5px; color: #4b5563; cursor: pointer; font-family: inherit; }
        .ac-btn-ghost:hover { background: #f6f7f9; color: var(--ac-ink, #14171c); }
        /* render our own glyph: the flat background would hide Bootstrap's SVG icon */
        .ac-modal .btn-close { width: 32px; height: 32px; flex: none; border: 1px solid #e6e8ec; background: #fff none; border-radius: 9px; opacity: 1; padding: 0; margin: 0; display: flex; align-items: center; justify-content: center; color: #6b7280; font-size: 19px; line-height: 1; font-family: inherit; box-shadow: none; }
        .ac-modal .btn-close::before { content: "\00d7"; }
        .ac-modal .btn-close:hover { background: #f6f7f9; color: #14171c; border-color: #d7dbe0; }

        @media (max-width: 575.98px) {
            .ac-toolbar { flex-direction: column; align-items: stretch; gap: 8px; padding: 10px; }
            .ac-tabs { width: 100%; }
            .ac-tab { flex: 1; padding: 8px 12px; font-size: 13px; }
            .ac-search-wrap { flex: 0 0 auto; flex-wrap: nowrap; justify-content: flex-start; gap: 8px; }
            .ac-search { flex: 1 1 auto; }
            .ac-search input { padding: 9px 34px 9px 10px; font-size: 13px; border-radius: 9px; }
            .ac-count { flex: none; font-size: 11.5px; }
            .ac-reset { flex: none; padding: 9px 11px; font-size: 12.5px; border-radius: 9px; }
            .ac-shell { gap: 14px; }
            .ac-title { font-size: 20px; }
            .ac-subtitle { font-size: 12.5px; margin-top: 4px; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            Livewire.on('lunchModal', function(name) {
                // Close all currently open modals
                $('.modal.show').each(function() {
                    let modalInstance = bootstrap.Modal.getInstance(this);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                });
                var myModal = new bootstrap.Modal(document.getElementById(name.name), {
                    keyboard: false
                });
                myModal.show();
            });

            // card keyboard activation
            $(document).on('keydown', '.ac-card', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $(this).trigger('click');
                }
            });
        });
    </script>
@endpush
