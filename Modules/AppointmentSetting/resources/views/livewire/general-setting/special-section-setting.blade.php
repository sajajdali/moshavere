<div>
    @php
        $general = $fetchData['GeneralAppointmentSetting'];
        $specials = $fetchData['SpecialAppointmentSetting'];
        // names and avatar come from the eager-loaded metas; the first_name/last_name accessors query each time
        $firstName = trim((string) $doctor->getMetas(\Modules\User\Enum\UserMetaEnum::FIRST_NAME)->last()?->meta_value);
        $lastName = trim((string) $doctor->getMetas(\Modules\User\Enum\UserMetaEnum::LAST_NAME)->last()?->meta_value);
        $doctorName = trim("{$firstName} {$lastName}") ?: 'بدون نام';
        $initials = collect([$firstName, $lastName])->map(fn ($part) => mb_substr($part, 0, 1))->filter()->implode(' ');
        $hasAvatar = filled($doctor->getMetas(\Modules\User\Enum\UserMetaEnum::AVATAR)->sortByDesc('created_at')->first()?->meta_value);
        $specialty = $doctor->specialities->pluck('title')->filter()->implode('، ');
    @endphp
    @includeIf('onlineconsultation::appointment-entry')
    <div class="presence-top">
        @if ($hasAvatar)
            <img src="{{ $doctor->getUserAvatar() }}" alt="" class="presence-avatar">
        @else
            <span class="presence-avatar presence-initials">{{ $initials ?: '؟' }}</span>
        @endif
        <div class="presence-doctor-name">
            <small>تنظیمات زمان‌های حضور</small>
            <h1>دکتر {{ $doctorName }}</h1>
            <div class="presence-doctor-meta">
                <small>{{ $specialty ?: 'تخصص ثبت نشده' }}</small>
                <small title="شناسه پزشک"><i class="fa-solid fa-hashtag" aria-hidden="true"></i>{{ $doctor->id }}</small>
                @if ($doctor->mobile)
                    <small dir="ltr" title="موبایل"><i class="fa-solid fa-mobile-screen" aria-hidden="true"></i>{{ $doctor->mobile }}</small>
                @endif
            </div>
        </div>
        <a href="{{ route('admin.appointment.doctor.list') }}" class="btn btn-sm btn-outline-secondary presence-back loading-btn">
            <i class="fa-solid fa-arrow-right me-1" aria-hidden="true"></i> بازگشت به لیست پزشکان
        </a>
    </div>
    @include('admin::layouts.components.alert')

    {{-- general setting: applies to every section without its own setting --}}
    <div class="card presence-card">
        <div class="card-header border-bottom presence-card-header">
            <div>
                <h3 class="card-title"><i class="fa-solid fa-calendar-week" aria-hidden="true"></i>زمان حضور عمومی</h3>
                <small>برای همه بخش‌هایی که تنظیم اختصاصی ندارند اعمال می‌شود.</small>
            </div>
            <button wire:click='editGeneralSetting' class="btn btn-sm btn-primary loading-btn"
                wire:loading.class='disable btn-loading bg-gray'>
                <i class="fa-solid fa-pen me-1" aria-hidden="true"></i> {{ $general ? 'ویرایش تنظیمات عمومی' : 'تنظیم زمان حضور عمومی' }}
            </button>
        </div>
        <div class="card-body">
            @if ($general)
                @include('appointmentsetting::components.generalsetting.presence-facts', ['facts' => $generalView['facts']])
                @include('appointmentsetting::components.generalsetting.week-schedule', ['days' => $generalView['days']])
                @include('appointmentsetting::components.generalsetting.presence-dates', ['dates' => $generalView['dates']])
            @else
                <div class="presence-empty">
                    <i class="fa-regular fa-calendar-xmark" aria-hidden="true"></i>
                    <p>هنوز زمان حضور عمومی برای این پزشک تنظیم نشده است.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- sections with their own days and hours --}}
    <div class="card presence-card">
        <div class="card-header border-bottom presence-card-header">
            <div>
                <h3 class="card-title"><i class="fa-solid fa-layer-group" aria-hidden="true"></i>بخش‌ها با تنظیمات اختصاصی <span class="presence-count">{{ $specials->count() }}</span></h3>
                <small>این بخش‌ها به جای زمان حضور عمومی، روز و ساعت مخصوص خود را دارند.</small>
            </div>
            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#ServiceAndPlaceModal">
                <i class="fa-solid fa-plus me-1" aria-hidden="true"></i> افزودن بخش اختصاصی
            </button>
        </div>
        <div class="card-body">
            @if ($specials->isNotEmpty())
                {{-- one tab per section; switching is client-side (Alpine), no request --}}
                <div x-data="{ active: {{ $specials->first()->id }} }">
                    <div class="presence-tabs">
                        <div class="presence-tab-list" role="tablist">
                            @foreach ($specials as $specialAppSetting)
                                <button type="button" role="tab" class="presence-tab {{ $loop->first ? 'is-active' : '' }}"
                                    wire:key="special-tab-{{ $specialAppSetting->id }}"
                                    :class="{ 'is-active': active === {{ $specialAppSetting->id }} }"
                                    :aria-selected="active === {{ $specialAppSetting->id }}"
                                    @click="active = {{ $specialAppSetting->id }}">
                                    <strong>
                                        <span class="presence-dot {{ $specialViews[$specialAppSetting->id]['facts']['active'] ? 'is-on' : 'is-off' }}"
                                            title="{{ $specialViews[$specialAppSetting->id]['facts']['active'] ? 'فعال' : 'غیرفعال' }}"></span>
                                        {{ $specialAppSetting->service->title }}
                                    </strong>
                                    @if ($specialAppSetting->place)
                                        <small><i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $specialAppSetting->place->title }}</small>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                        {{-- actions of the selected tab; each section keeps its own buttons so the
                             delete confirm (which caches data-id through jQuery) always gets the right id --}}
                        <div class="presence-tab-actions">
                            @foreach ($specials as $specialAppSetting)
                                <div x-show="active === {{ $specialAppSetting->id }}" @if (! $loop->first) style="display: none" @endif>
                                    <button type="button" class="btn btn-sm btn-outline-primary loading-btn"
                                        wire:click='editSpecialSection({{ $specialAppSetting->id }})'>
                                        <i class="fa-solid fa-pen me-1" aria-hidden="true"></i> ویرایش
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete_confirm_alert"
                                        data-label="تنظیمات اختصاصی {{ $specialAppSetting->service->title }}" data-id="{{ $specialAppSetting->id }}">
                                        <i class="fa-solid fa-trash me-1" aria-hidden="true"></i> حذف
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @foreach ($specials as $specialAppSetting)
                        <div class="presence-tab-panel" role="tabpanel" wire:key="special-panel-{{ $specialAppSetting->id }}"
                            x-show="active === {{ $specialAppSetting->id }}" @if (! $loop->first) style="display: none" @endif>
                            @include('appointmentsetting::components.generalsetting.presence-facts', ['facts' => $specialViews[$specialAppSetting->id]['facts']])
                            @include('appointmentsetting::components.generalsetting.week-schedule', ['days' => $specialViews[$specialAppSetting->id]['days']])
                            @include('appointmentsetting::components.generalsetting.presence-dates', ['dates' => $specialViews[$specialAppSetting->id]['dates']])
                        </div>
                    @endforeach
                </div>
            @else
                <div class="presence-empty">
                    <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                    <p>هیچ بخشی ساعت اختصاصی ندارد؛ همه بخش‌ها از زمان حضور عمومی استفاده می‌کنند.</p>
                    <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#ServiceAndPlaceModal">
                        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i> افزودن اولین بخش اختصاصی
                    </button>
                </div>
            @endif
        </div>
    </div>
    <livewire:appointmentsetting::general-setting.modal.service-and-place-modal :doctor="$doctor" />
</div>

@push('styles')
    <style>
        p { font-size: medium; }
        /* doctor */
        .presence-top {
            display: flex; flex-wrap: wrap; align-items: center; gap: 14px; margin: 20px 0; padding: 16px 18px;
            border: 1px solid rgba(128, 128, 160, .2); border-inline-start: 4px solid var(--primary-bg-color, #0070bb);
            border-radius: 12px; background: #fff;
        }
        .dark-mode .presence-top { background: transparent; }
        .presence-avatar { width: 56px; height: 56px; flex: 0 0 56px; border-radius: 50%; object-fit: cover; }
        .presence-initials {
            display: inline-flex; align-items: center; justify-content: center; font-size: 17px; font-weight: 700;
            color: var(--primary-bg-color, #0070bb); background: color-mix(in srgb, var(--primary-bg-color, #0070bb) 14%, transparent);
        }
        .presence-doctor-name { flex: 1; min-width: 180px; display: flex; flex-direction: column; }
        .presence-doctor-name h1 { margin: 0; font-size: 20px; font-weight: 800; line-height: 1.5; color: #16213a; }
        .presence-doctor-name small { font-size: 12.5px; color: #6b7385; }
        .presence-doctor-meta { display: flex; flex-wrap: wrap; gap: 2px 14px; }
        .presence-doctor-meta i { margin-left: 5px; opacity: .6; }
        .dark-mode .presence-doctor-name h1 { color: #eef1f8; }
        .dark-mode .presence-doctor-name small { color: #a9b1c4; }
        /* cards */
        .presence-card { border-radius: 12px; }
        .presence-card-header { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; }
        .presence-card-header .card-title { display: flex; align-items: center; gap: 8px; margin-bottom: 2px; font-size: 16px; }
        .presence-card-header .card-title > i { color: var(--primary-bg-color, #0070bb); }
        .presence-card-header small { font-size: 12.5px; color: #6b7385; }
        .dark-mode .presence-card-header small { color: #a9b1c4; }
        .presence-count {
            display: inline-block; min-width: 22px; padding: 0 7px; border-radius: 11px;
            font-size: 12px; line-height: 22px; text-align: center; background: rgba(128, 128, 160, .12);
        }
        /* facts */
        .presence-facts { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; }
        .presence-facts > span { display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 8px; font-size: 12.5px; background: rgba(128, 128, 160, .1); }
        .presence-facts > span > i { opacity: .6; font-size: 12px; }
        .presence-facts > .is-on { color: #1a9e5b; background: rgba(26, 158, 91, .1); }
        .presence-facts > .is-off { color: #d9434b; background: rgba(217, 67, 75, .1); }
        .presence-facts > .is-pay { color: var(--primary-bg-color, #0070bb); background: color-mix(in srgb, var(--primary-bg-color, #0070bb) 9%, transparent); }
        .presence-facts > .is-on > i, .presence-facts > .is-off > i, .presence-facts > .is-pay > i { opacity: 1; }
        /* week */
        .presence-week { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 8px; }
        .presence-day {
            position: relative; display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 10px 6px; border-radius: 10px;
            background: color-mix(in srgb, var(--primary-bg-color, #0070bb) 9%, transparent);
            border: 1px solid color-mix(in srgb, var(--primary-bg-color, #0070bb) 22%, transparent);
        }
        .presence-day.is-closed { background: transparent; border-color: rgba(128, 128, 160, .18); }
        /* today stands out with a solid outline */
        .presence-day.is-today { border: 2px solid var(--primary-bg-color, #0070bb); }
        .presence-day-name { display: flex; align-items: center; gap: 5px; font-size: 13px; font-weight: 700; }
        .presence-today { padding: 0 6px; border-radius: 6px; font-size: 10.5px; font-weight: 600; color: #fff; background: var(--primary-bg-color, #0070bb); }
        .presence-range { font-size: 12.5px; font-weight: 600; white-space: nowrap; color: var(--primary-bg-color, #0070bb); }
        .presence-day.is-closed .presence-range { font-weight: 400; color: inherit; opacity: .4; }
        .presence-dates { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; margin-top: 14px; font-size: 13px; }
        .presence-dates > span { padding: 3px 10px; border-radius: 8px; background: rgba(247, 183, 49, .14); }
        /* special sections as tabs */
        /* tabs wrap onto more lines instead of scrolling */
        .presence-tabs {
            display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 12px 16px;
            margin-bottom: 18px; padding-bottom: 16px; border-bottom: 1px solid rgba(128, 128, 160, .2);
        }
        .presence-tab-list { display: flex; flex-wrap: wrap; gap: 10px; flex: 1 1 auto; min-width: 0; }
        .presence-tab {
            display: flex; flex-direction: column; align-items: flex-start; justify-content: center; gap: 3px;
            min-width: 180px; padding: 12px 18px; border-radius: 12px; cursor: pointer; text-align: right;
            border: 1px solid rgba(128, 128, 160, .25); background: transparent; color: inherit;
            transition: border-color .15s, background .15s, color .15s;
        }
        .presence-tab:hover { border-color: color-mix(in srgb, var(--primary-bg-color, #0070bb) 55%, transparent); }
        .presence-tab strong { display: flex; align-items: center; gap: 7px; font-size: 16px; font-weight: 800; line-height: 1.4; }
        .presence-tab small { font-size: 13px; opacity: .65; }
        .presence-tab small i { margin-left: 5px; }
        .presence-tab.is-active {
            color: var(--primary-bg-color, #0070bb); border-color: var(--primary-bg-color, #0070bb);
            background: color-mix(in srgb, var(--primary-bg-color, #0070bb) 10%, transparent);
            box-shadow: inset 0 0 0 1px var(--primary-bg-color, #0070bb);
        }
        .presence-tab.is-active small { opacity: .85; }
        .presence-dot { width: 8px; height: 8px; flex: 0 0 8px; border-radius: 50%; }
        .presence-dot.is-on { background: #1a9e5b; }
        .presence-dot.is-off { background: #d9434b; }
        .presence-tab-actions { display: flex; align-self: center; }
        .presence-tab-actions > div { display: flex; gap: 8px; }
        .presence-empty { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 22px 12px; text-align: center; }
        .presence-empty > i { font-size: 30px; opacity: .3; }
        .presence-empty p { margin: 0; font-size: 13.5px !important; color: #6b7385; }
        @media (max-width: 991.98px) { .presence-week { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        @media (max-width: 575.98px) {
            .presence-week { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .presence-back { width: 100%; }
            .presence-tab { flex: 1 1 100%; min-width: 0; }
            .presence-tab-actions, .presence-tab-actions > div { width: 100%; }
            .presence-tab-actions .btn { flex: 1; }
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/admin.sweetalert.js') }}"></script>
    <script>
        $(document).ready(function() {
            Livewire.on('closeModal', function() {
                var myModalEl = document.querySelector('#changeDocmodal')
                var modal = bootstrap.Modal.getOrCreateInstance(myModalEl)
                modal.hide();
            });
        });
    </script>
@endpush
