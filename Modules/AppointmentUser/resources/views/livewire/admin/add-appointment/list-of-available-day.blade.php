@php
    $days = $fethData['firstTreeAvailableAppointment'] ?? [];
    $hasDays = !empty($days);
    $dayKeys = array_keys($days);
@endphp

<div class="ad-page" dir="rtl">
    <style>
        .ad-page{color:#14171c;font-size:14px}
        .ad-shell{max-width:1180px;margin:0 auto;display:flex;flex-direction:column;gap:18px}

        .ad-breadcrumb{display:flex;align-items:center;gap:8px;font-size:12.5px;color:#8b9099;margin-bottom:2px}
        .ad-breadcrumb .sep{color:#c3c7ce}
        .ad-breadcrumb .cur{color:#14171c}
        .ad-title{margin:0;font-size:25px;font-weight:700;letter-spacing:-.01em}

        .ad-docbar{background:#fff;border:1px solid #e6e8ec;border-radius:14px;padding:14px 16px;display:flex;flex-wrap:wrap;align-items:center;gap:16px;justify-content:space-between}
        .ad-docbar-id{display:flex;align-items:center;gap:12px;min-width:0}
        .ad-docbar-icon{width:42px;height:42px;flex:none;border-radius:12px;background:repeating-linear-gradient(135deg,#eaf1f0 0 6px,#e0eae8 6px 12px);display:flex;align-items:center;justify-content:center;box-shadow:inset 0 0 0 1px rgba(15,118,110,.12);color:#0f766e;font-size:17px}
        .ad-docbar-text{display:flex;flex-direction:column;gap:3px;min-width:0}
        .ad-docbar-name{font-size:15px;font-weight:600}
        .ad-docbar-meta{font-size:12.5px;color:#6b7280}
        .ad-custom-trigger{position:relative;display:flex;flex-direction:column;align-items:flex-end;gap:6px}
        .ad-btn-custom{padding:9px 14px;border:1px solid #e0e3e8;border-radius:10px;font-size:13px;color:#4b5563;background:#fff;cursor:pointer;font-family:inherit;white-space:nowrap}
        .ad-btn-custom:hover{background:#f6f7f9;color:#14171c}
        /* jalaliDatepicker positions its popup against this field's own layout box, so it needs
           real dimensions and to sit in normal flow — visually hidden via opacity, not display/size */
        .ad-custom-input{position:absolute;inset-inline-end:0;top:calc(100% + 6px);width:180px;height:38px;padding:0;border:none;opacity:0;z-index:-1}

        .ad-days-row{display:flex;align-items:center;gap:10px}
        .ad-days-track{flex:1;min-width:0;display:grid;grid-auto-flow:column;grid-auto-columns:minmax(112px,1fr);gap:10px;overflow-x:auto;padding-bottom:2px;scrollbar-width:thin}
        .ad-day{display:flex;flex-direction:column;gap:6px;align-items:stretch;text-align:start;padding:13px 14px;border-radius:13px;cursor:pointer;transition:all .15s;background:#fff;color:#14171c;border:1px solid #e6e8ec;font-family:inherit}
        .ad-day .ad-day-top{display:flex;align-items:center;justify-content:space-between;gap:8px}
        .ad-day .ad-day-weekday{font-size:14px;font-weight:600}
        .ad-day .ad-day-dot{width:7px;height:7px;flex:none;border-radius:50%;background:#0d9488}
        .ad-day .ad-day-date{font-size:12.5px;opacity:.7;font-feature-settings:'tnum'}
        .ad-day .ad-day-count{font-size:11.5px;font-feature-settings:'tnum';color:#0f766e}
        .ad-day.active{background:#0d9488;color:#fff;border-color:#0d9488;box-shadow:0 8px 20px -14px rgba(13,148,136,.9)}
        .ad-day.active .ad-day-dot{background:rgba(255,255,255,.85)}
        .ad-day.active .ad-day-count{color:rgba(255,255,255,.9)}

        .ad-panel{background:#fff;border:1px solid #e6e8ec;border-radius:16px;overflow:hidden}
        .ad-panel-head{padding:16px 20px;border-bottom:1px solid #f0f1f4;display:flex;flex-wrap:wrap;align-items:baseline;gap:10px;justify-content:space-between}
        .ad-panel-head-left{display:flex;align-items:baseline;gap:10px}
        .ad-panel-title{font-size:16px;font-weight:600}
        .ad-panel-date{font-size:12.5px;color:#8b9099;font-feature-settings:'tnum'}
        .ad-panel-count{font-size:12.5px;color:#6b7280;font-feature-settings:'tnum'}

        .ad-groups{padding:6px 20px 20px;display:flex;flex-direction:column}
        .ad-group{padding:18px 0;border-bottom:1px solid #f4f5f7;display:flex;flex-wrap:wrap;gap:16px;align-items:flex-start}
        .ad-group:last-child{border-bottom:none}
        .ad-group-label{flex:0 0 132px;display:flex;flex-direction:column;gap:3px;padding-top:4px}
        .ad-group-label span:first-child{font-size:13.5px;font-weight:600}
        .ad-group-range{font-size:11.5px;color:#9aa0a8;font-feature-settings:'tnum';unicode-bidi:isolate;direction:ltr;text-align:start}
        .ad-group-slots{flex:1 1 300px;display:flex;flex-wrap:wrap;gap:8px}
        .ad-slot{padding:9px 14px;min-width:70px;border-radius:10px;font-size:13.5px;font-weight:500;font-feature-settings:'tnum';cursor:pointer;transition:all .12s;border:1px solid #dfe3e7;background:#fff;color:#14171c;font-family:inherit}
        .ad-slot:hover{border-color:#0d9488;background:#f0faf8;color:#0f766e}
        .ad-slot.is-loading{opacity:.6;pointer-events:none}

        .ad-empty-slots{padding:56px 24px;text-align:center;color:#6b7280}
        .ad-empty-slots strong{display:block;font-weight:600;color:#14171c;margin-bottom:6px}
        .ad-empty-slots span{font-size:13px}

        .ad-panel-foot{padding:14px 20px;border-top:1px solid #f0f1f4;background:#fbfcfc;display:flex;flex-wrap:wrap;align-items:center;gap:12px;justify-content:space-between}
        .ad-panel-foot span{font-size:12.5px;color:#6b7280}
        .ad-more-btn{padding:9px 16px;border:1px solid #0d9488;border-radius:10px;background:#fff;color:#0f766e;font-size:13px;font-weight:500;cursor:pointer;display:flex;align-items:center;gap:7px;font-family:inherit}
        .ad-more-btn:hover{background:#f0faf8}
        .ad-more-btn.is-loading{opacity:.6;pointer-events:none}

        .ad-empty-page{background:#fff;border:1px dashed #d7dbe0;border-radius:16px;padding:48px 24px;text-align:center;color:#6b7280}
        .ad-empty-page strong{display:block;font-weight:600;color:#14171c;margin-bottom:6px}
        .ad-empty-page span{font-size:13px}

        .ad-field-error{font-size:12px;color:#c0392b;white-space:nowrap}

        @media (max-width:575px){
            .ad-docbar{flex-direction:column;align-items:stretch}
            .ad-btn-custom{width:100%;text-align:center}
            .ad-group{flex-direction:column;gap:8px}
            .ad-group-label{flex:none}
        }
    </style>

    <div class="ad-shell">
        @include('admin::layouts.components.alert')

        <div>
            <div class="ad-breadcrumb">
                <span>ثبت نوبت</span><span class="sep">/</span><span>انتخاب پزشک</span><span class="sep">/</span><span
                    class="cur">انتخاب روز و ساعت</span>
            </div>
            <h1 class="ad-title">انتخاب روز و ساعت</h1>
        </div>

        <div class="ad-docbar">
            <div class="ad-docbar-id">
                <span class="ad-docbar-icon"><i class="fa fa-user-md" aria-hidden="true"></i></span>
                <span class="ad-docbar-text">
                    <span class="ad-docbar-name">
                        {{ $fethData['doctor']->speciality_type == 1 ? 'دکتر' : '' }} {{ $fethData['doctor']?->full_name }}
                    </span>
                    <span class="ad-docbar-meta">{{ $fethData['service']?->title }}</span>
                </span>
            </div>
            <div class="ad-custom-trigger">
                <button type="button" class="ad-btn-custom" id="customDateTrigger">افزودن نوبت برای تاریخ انتخابی</button>
                <input type="text" class="ad-custom-input" placeholder="۱۴۰۵/۰۶/۲۵" data-jdp
                    autocomplete="off" data-name="specificDayDate" id="customDateInput" tabindex="-1">
                @error('specificDayDate')
                    <span class="ad-field-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        @if ($hasDays)
            <div class="ad-days-row">
                <div class="ad-days-track" id="adDaysTrack">
                    @foreach ($days as $dateOfDay => $availableAppointments)
                        <button type="button" class="ad-day @if ($loop->first) active @endif"
                            data-day-target="ad-day-panel-{{ $loop->index }}" wire:key='day-tab-{{ $dateOfDay }}'>
                            <span class="ad-day-top">
                                <span class="ad-day-weekday">{{ verta($dateOfDay)->format('l') }}</span>
                                <span class="ad-day-dot"></span>
                            </span>
                            <span class="ad-day-date">{{ verta($dateOfDay)->format('y/m/d') }}</span>
                            <span class="ad-day-count">{{ count($availableAppointments) }} نوبت آزاد</span>
                        </button>
                    @endforeach
                </div>
            </div>

            @foreach ($days as $dateOfDay => $availableAppointments)
                @php
                    $morning = collect($availableAppointments)->filter(fn($s) => (int) substr($s['from'], 0, 2) < 12)->values();
                    $evening = collect($availableAppointments)->filter(fn($s) => (int) substr($s['from'], 0, 2) >= 12)->values();
                    $groups = collect([
                        ['label' => 'صبح', 'slots' => $morning],
                        ['label' => 'عصر', 'slots' => $evening],
                    ])->filter(fn($g) => $g['slots']->isNotEmpty());
                @endphp
                <div class="ad-panel" id="ad-day-panel-{{ $loop->index }}"
                    style="{{ $loop->first ? '' : 'display:none' }}" wire:key='day-panel-{{ $dateOfDay }}'>
                    <div class="ad-panel-head">
                        <div class="ad-panel-head-left">
                            <span class="ad-panel-title">نوبت‌های {{ verta($dateOfDay)->format('l') }}</span>
                            <span class="ad-panel-date">{{ verta($dateOfDay)->format('y/m/d') }}</span>
                        </div>
                        <span class="ad-panel-count">{{ count($availableAppointments) }} زمان آزاد</span>
                    </div>

                    @if ($groups->isNotEmpty())
                        <div class="ad-groups">
                            @foreach ($groups as $group)
                                <div class="ad-group">
                                    <div class="ad-group-label">
                                        <span>{{ $group['label'] }}</span>
                                        <span class="ad-group-range">{{ $group['slots']->first()['from'] }} –
                                            {{ $group['slots']->last()['until'] }}</span>
                                    </div>
                                    <div class="ad-group-slots">
                                        @foreach ($group['slots'] as $slot)
                                            <button type="button" class="ad-slot" wire:loading.class='is-loading'
                                                wire:target="GotoAppointmentList('{{ $dateOfDay }}' ,'{{ $slot['from'] }}')"
                                                wire:click="GotoAppointmentList('{{ $dateOfDay }}' ,'{{ $slot['from'] }}')">
                                                {{ substr($slot['from'], 0, -3) }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="ad-empty-slots">
                            <strong>این روز نوبت آزادی ندارد</strong>
                            <span>روز دیگری از نوار بالا انتخاب کنید یا ساعت دلخواه را وارد کنید.</span>
                        </div>
                    @endif

                    <div class="ad-panel-foot">
                        <span>ساعت مورد نظر در فهرست بالا نیست؟</span>
                        <button type="button" class="ad-more-btn" wire:loading.class='is-loading'
                            wire:target="GotoAppointmentList('{{ $dateOfDay }}')"
                            wire:click="GotoAppointmentList('{{ $dateOfDay }}')">
                            <i class="fa fa-clock-o" aria-hidden="true"></i>
                            <span>ثبت نوبت در ساعت دیگر</span>
                        </button>
                    </div>
                </div>
            @endforeach
        @else
            <div class="ad-empty-page">
                <strong>نوبت خالی یافت نشد</strong>
                <span>در روزهای آینده نوبتی برای این پزشک ثبت نشده است.</span>
            </div>
        @endif
    </div>
</div>

@push('scripts')
    <script>
        const iranianHolidays = @json(holidays_array());
        jalaliDatepicker.startWatch({
            zIndex: 99999,
            dayRendering: function(dayOptions, input) {
                const formatted =
                    `${dayOptions.year}/${String(dayOptions.month).padStart(2, '0')}/${String(dayOptions.day).padStart(2, '0')}`;
                const isHoliday = iranianHolidays.includes(formatted);
                return {
                    isHollyDay: isHoliday,
                };
            }
        });
        $(document).on('input', '[data-jdp]', function() {
            let selectedDate = $(this).val();
            let seterValue = $(this).data('name');
            @this.set(seterValue, selectedDate);
        });

        // The datepicker opens on focus, so a synthetic click on the hidden input is not enough.
        $(document).off('click.appointmentCustomDate', '#customDateTrigger')
            .on('click.appointmentCustomDate', '#customDateTrigger', function() {
                const input = document.getElementById('customDateInput');

                if (input) {
                    jalaliDatepicker.show(input);
                }
            });

        // jalaliDatepicker emits change after a day is selected.
        $(document).off('change.appointmentCustomDate', '#customDateInput[data-jdp]')
            .on('change.appointmentCustomDate', '#customDateInput[data-jdp]', function() {
                const selectedDate = $(this).val();
                if (!selectedDate) return;

                @this.set('specificDayDate', selectedDate).then(() => @this.call('GotoSpecificDay'));
            });

        $(document).on('click', '#adDaysTrack .ad-day', function() {
            const target = $(this).data('day-target');
            $('#adDaysTrack .ad-day').removeClass('active');
            $(this).addClass('active');
            $('.ad-panel').hide();
            $('#' + target).show();
        });
    </script>
@endpush
