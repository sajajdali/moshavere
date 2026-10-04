<div>
    @includeIf('onlineconsultation::appointment-entry')
    <div class="page-header mb-5">
        <div>
            <h1 class="page-title">تنظیمات زمان های حضور</h1>
        </div>
    </div>
    @include('admin::layouts.components.alert')
    <div class="card doctor-picker">
        <div class="card-header border-bottom doctor-picker-header">
            <div>
                <h3 class="card-title mb-1">
                    انتخاب پزشک
                    @if (isset($doctors) && ! $form['services'] && ! $form['place'])
                        <span class="doctor-picker-count">{{ number_format($doctors->total()) }} پزشک</span>
                    @endif
                </h3>
                <small class="text-muted">برای تنظیم روزها و ساعت‌های حضور، پزشک مورد نظر را انتخاب کنید.</small>
            </div>
            @if (isset($doctors) && ! $form['services'] && ! $form['place'])
                <div class="doctor-picker-search">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input type="search" class="form-control" wire:model.live.debounce.400ms="search.q"
                        placeholder="جستجو: نام، نام خانوادگی، موبایل یا شناسه" aria-label="جستجوی پزشک">
                    <span class="doctor-picker-spinner" wire:loading wire:target="search.q">
                        <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
                    </span>
                    @if (filled($search['q'] ?? null))
                        <button type="button" class="doctor-picker-clear" wire:click="resetProperties" wire:loading.remove wire:target="search.q"
                            title="پاک کردن جستجو" aria-label="پاک کردن جستجو">
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                        </button>
                    @endif
                </div>
            @endif
        </div>
        <div class="card-body">
            @if (! isset($doctors))
                <div class="doctor-picker-empty">
                    <i class="fa-solid fa-user-doctor" aria-hidden="true"></i>
                    <strong>پزشکی یافت نشد</strong>
                    <p>لطفا ابتدا پزشک به سیستم اضافه کنید.</p>
                    <a href="{{ route('admin.user.create') }}" class="btn btn-primary">افزودن پزشک جدید</a>
                </div>
            @elseif ($form['services'])
                <div class="doctor-picker-empty">
                    <i class="fa-solid fa-briefcase-medical" aria-hidden="true"></i>
                    <strong>بخشی یافت نشد</strong>
                    <p>برای تنظیم زمان حضور، ابتدا بخش (سرویس) به سیستم اضافه کنید.</p>
                    @can('create', \Modules\Service\app\Models\Service::class)
                        <a href="{{ route('admin.service.create') }}" class="btn btn-primary">افزودن بخش جدید</a>
                    @endcan
                </div>
            @elseif ($form['place'])
                <div class="doctor-picker-empty">
                    <i class="fa-solid fa-hospital" aria-hidden="true"></i>
                    <strong>مطبی یافت نشد</strong>
                    <p>برای تنظیم زمان حضور، ابتدا مطب به سیستم اضافه کنید.</p>
                    <a href="{{ route('admin.place.create') }}" class="btn btn-primary">افزودن مطب جدید</a>
                </div>
            @elseif ($doctors->isEmpty())
                <div class="doctor-picker-empty">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <strong>پزشکی با این مشخصات پیدا نشد</strong>
                    <p>عبارت جستجو را تغییر دهید.</p>
                    <button type="button" class="btn btn-outline-primary" wire:click="resetProperties">نمایش همه پزشکان</button>
                </div>
            @else
                {{-- one doctor per row: a long horizontal card --}}
                <div class="doctor-rows" wire:loading.class="opacity-50" wire:target="search.q, resetProperties, gotoPage, previousPage, nextPage">
                    @foreach ($doctors as $doctor)
                        @php
                            $settingUrl = route('admin.appointment.setting', ['user' => $doctor->id]);
                            // names and avatar come from the eager-loaded metas; the first_name/last_name accessors query per doctor
                            $firstName = trim((string) $doctor->getMetas(\Modules\User\Enum\UserMetaEnum::FIRST_NAME)->last()?->meta_value);
                            $lastName = trim((string) $doctor->getMetas(\Modules\User\Enum\UserMetaEnum::LAST_NAME)->last()?->meta_value);
                            $fullName = trim("{$firstName} {$lastName}");
                            $initials = collect([$firstName, $lastName])->map(fn ($part) => mb_substr($part, 0, 1))->filter()->implode(' ');
                            $hasAvatar = filled($doctor->getMetas(\Modules\User\Enum\UserMetaEnum::AVATAR)->sortByDesc('created_at')->first()?->meta_value);
                            $specialty = $doctor->specialities->pluck('title')->filter()->implode('، ');
                        @endphp
                        <div class="doctor-row {{ $doctor->has_general_setting ? '' : 'is-unset' }}" wire:key="doctor-{{ $doctor->id }}">
                            <div class="doctor-row-who">
                                @if ($hasAvatar)
                                    <img src="{{ $doctor->getUserAvatar() }}" alt="" class="doctor-tile-avatar" loading="lazy">
                                @else
                                    <span class="doctor-tile-avatar doctor-tile-initials">{{ $initials ?: '؟' }}</span>
                                @endif
                                <div class="doctor-tile-name">
                                    {{-- stretched-link makes the whole row clickable --}}
                                    <a href="{{ $settingUrl }}" class="stretched-link loading-btn" title="{{ $fullName }}">
                                        <span class="doctor-tile-prefix">دکتر</span>
                                        {{ $fullName ?: 'بدون نام' }}
                                    </a>
                                    <small>{{ $specialty ?: 'تخصص ثبت نشده' }}</small>
                                </div>
                            </div>
                            <div class="doctor-row-facts">
                                <span title="شناسه پزشک"><i class="fa-solid fa-hashtag" aria-hidden="true"></i>{{ $doctor->id }}</span>
                                @if ($doctor->mobile)
                                    <span dir="ltr" title="موبایل"><i class="fa-solid fa-mobile-screen" aria-hidden="true"></i>{{ $doctor->mobile }}</span>
                                @endif
                            </div>
                            <div class="doctor-row-stats">
                                <span><i class="fa-solid fa-layer-group" aria-hidden="true"></i><strong>{{ $doctor->service_count }}</strong> بخش</span>
                                <span><i class="fa-regular fa-clock" aria-hidden="true"></i><strong>{{ $doctor->special_settings_count }}</strong> زمان اختصاصی</span>
                            </div>
                            <div class="doctor-row-status">
                                @if ($doctor->has_general_setting)
                                    <span class="badge bg-success-transparent text-success">
                                        <i class="fa-solid fa-circle-check me-1" aria-hidden="true"></i>تنظیم شده
                                    </span>
                                @else
                                    <span class="badge bg-warning-transparent text-warning">
                                        <i class="fa-solid fa-clock me-1" aria-hidden="true"></i>تنظیم نشده
                                    </span>
                                @endif
                            </div>
                            <a href="{{ $settingUrl }}" class="btn btn-sm {{ $doctor->has_general_setting ? 'btn-outline-primary' : 'btn-primary' }} doctor-row-action loading-btn">
                                <i class="fa-solid {{ $doctor->has_general_setting ? 'fa-pen-to-square' : 'fa-calendar-plus' }} me-1" aria-hidden="true"></i>
                                {{ $doctor->has_general_setting ? 'ویرایش زمان‌های حضور' : 'تنظیم زمان‌های حضور' }}
                            </a>
                        </div>
                    @endforeach
                </div>
                <div class="d-flex justify-content-center mt-4">
                    {{ $doctors->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@push('styles')
    <style>
        .doctor-picker-header { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between; }
        .doctor-picker-header .card-title { display: flex; align-items: center; gap: 8px; }
        .doctor-picker-count {
            padding: 2px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;
            color: var(--primary-bg-color, #0070bb); background: color-mix(in srgb, var(--primary-bg-color, #0070bb) 10%, transparent);
        }
        .doctor-picker-search { position: relative; flex: 0 1 360px; min-width: 220px; }
        .doctor-picker-search .form-control { padding-right: 38px; padding-left: 36px; border-radius: 10px; }
        .doctor-picker-search .form-control::-webkit-search-cancel-button { display: none; }
        .doctor-picker-search > .fa-magnifying-glass { position: absolute; right: 13px; top: 50%; transform: translateY(-50%); opacity: .45; pointer-events: none; }
        .doctor-picker-spinner { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); opacity: .6; }
        .doctor-picker-clear {
            position: absolute; left: 8px; top: 50%; transform: translateY(-50%); display: grid; place-items: center;
            width: 24px; height: 24px; padding: 0; border: 0; border-radius: 50%; background: rgba(128, 128, 160, .14); color: inherit; cursor: pointer;
        }
        .doctor-picker-clear:hover { background: rgba(128, 128, 160, .26); }
        /* one doctor per row: who | id & mobile | counts | status | action */
        .doctor-rows { display: flex; flex-direction: column; gap: 10px; transition: opacity .15s; }
        .doctor-row {
            position: relative; cursor: pointer;
            display: grid; align-items: center; gap: 12px 20px; padding: 12px 16px;
            grid-template-columns: minmax(220px, 2.2fr) minmax(150px, 1fr) minmax(190px, 1.1fr) auto auto;
            border: 1px solid rgba(128, 128, 160, .22); border-inline-start: 4px solid var(--primary-bg-color, #0070bb);
            border-radius: 12px; transition: box-shadow .15s, border-color .15s;
        }
        /* doctors without a general setting get a warning accent so they stand out */
        .doctor-row.is-unset { border-inline-start-color: #f7b731; }
        .doctor-row:hover { border-color: color-mix(in srgb, var(--primary-bg-color, #0070bb) 55%, transparent); box-shadow: 0 6px 18px -12px rgba(40, 40, 80, .35); }
        .doctor-row:hover, .doctor-row.is-unset:hover { border-inline-start-color: var(--primary-bg-color, #0070bb); }
        .doctor-row:focus-within { border-color: var(--primary-bg-color, #0070bb); }
        .doctor-row .stretched-link:focus { outline: none; }
        /* the action button stays clickable above the row-wide link */
        .doctor-row-action { position: relative; z-index: 2; white-space: nowrap; }
        .doctor-row-who { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .doctor-row-facts { display: flex; flex-direction: column; gap: 2px; font-size: 13px; }
        .doctor-row-facts > span { opacity: .75; white-space: nowrap; }
        .doctor-row-facts i { margin-left: 6px; opacity: .6; }
        .doctor-row-stats { display: flex; flex-wrap: wrap; gap: 6px; }
        .doctor-row-stats > span { padding: 4px 10px; border-radius: 8px; font-size: 12.5px; white-space: nowrap; background: rgba(128, 128, 160, .08); }
        .doctor-row-stats i { margin-left: 5px; opacity: .55; font-size: 12px; }
        .doctor-row-stats strong { font-size: 14px; margin-left: 2px; }
        .doctor-row-status .badge { font-weight: 600; white-space: nowrap; }
        @media (max-width: 1199.98px) {
            .doctor-row { grid-template-columns: minmax(200px, 1fr) auto auto; }
            .doctor-row-facts { flex-direction: row; flex-wrap: wrap; gap: 4px 14px; grid-column: 1 / -1; order: 5; }
            .doctor-row-stats { grid-column: 1 / -1; order: 6; }
        }
        @media (max-width: 575.98px) {
            .doctor-picker-search { flex-basis: 100%; }
            .doctor-row { grid-template-columns: 1fr auto; }
            .doctor-row-action { grid-column: 1 / -1; order: 7; width: 100%; }
        }
        .doctor-tile-avatar { width: 48px; height: 48px; flex: 0 0 48px; border-radius: 50%; object-fit: cover; }
        .doctor-tile-initials {
            display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 15px;
            color: var(--primary-bg-color, #0070bb); background: color-mix(in srgb, var(--primary-bg-color, #0070bb) 14%, transparent);
        }
        .doctor-tile-name { min-width: 0; flex: 1; display: flex; flex-direction: column; gap: 3px; }
        /* names wrap to two lines instead of being cut off */
        .doctor-tile-name a {
            font-weight: 800; font-size: 17px; line-height: 1.55; color: #16213a;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; overflow-wrap: anywhere;
        }
        .doctor-tile-name a:hover { color: var(--primary-bg-color, #0070bb); }
        .doctor-tile-prefix { font-weight: 500; font-size: 14px; opacity: .6; }
        .doctor-tile-name small { font-size: 13px; color: #5b6477; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dark-mode .doctor-tile-name a { color: #eef1f8; }
        .dark-mode .doctor-tile-name a:hover { color: var(--primary-bg-color, #0070bb); }
        .dark-mode .doctor-tile-name small { color: #a9b1c4; }
        .doctor-picker-empty { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 40px 16px; text-align: center; }
        .doctor-picker-empty > i { font-size: 34px; opacity: .35; margin-bottom: 6px; }
        .doctor-picker-empty p { margin: 0 0 10px; opacity: .7; }
    </style>
@endpush
