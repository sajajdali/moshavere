<div class="sd-section @error('form.specialDaytimeValues.*') border border-danger @enderror">
    <div class="card-header border-bottom d-flex justify-content-between">
        <h3>
            <i class="fa fa-calendar-plus-o me-2 d-none d-sm-inline" aria-hidden="true"></i>
            <span>
                تغییر ساعت حضور برای<span class="text-primary"> یک روز خاص </span>
            </span>
        </h3>
        <div class="main-toggle-group d-sm-flex align-items-center ms-0">
            <div class="toggle toggle-lg toggle-primary my-1  customCheckbox @if (isset($form['specialDaytimeValues']) && count($form['specialDaytimeValues']) >= 1) on  @else off @endif"
                data-id="specialDayTimeSetting" wire:ignore.self data-bs-toggle="collapse" href="#specialDayTimeSetting"
                role="button" aria-expanded="false" aria-controls="specialDayTimeSetting">
                <span></span>
            </div>
        </div>
    </div>
    <div class="collapse @if (isset($form['specialDaytimeValues']) && count($form['specialDaytimeValues']) >= 1) show @endif " id="specialDayTimeSetting" wire:ignore.self>
        <div class="card-body">
            @error('form.specialDaytimeValues.*')
                <div class="alert alert-danger" role="alert">
                    <p class="text-danger mb-0"> در صورت انتخاب تاریخ ، لازم هست که ساعت های مخصوص آن روز را نیز انتخاب کنید
                    </p>
                </div>
            @enderror

            <div class="sd-toolbar">
                <div class="sd-toolbar-text">
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                    <span>تاریخ روزی را که ساعت حضور آن با برنامه‌ی هفتگی بالا متفاوت است انتخاب کنید. روزهای گذشته به‌صورت خودکار حذف می‌شوند.</span>
                </div>
                <div class="sd-toolbar-actions">
                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="اضافه کردن روز"
                        wire:click="addFormCounter('specialDaySetting')" class="btn btn-primary btn-sm">
                        <span wire:loading.remove wire:target="addFormCounter('specialDaySetting')">
                            <i class="fa fa-plus me-1" aria-hidden="true"></i> اضافه کردن روز
                        </span>
                        <span wire:loading wire:target="addFormCounter('specialDaySetting')">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </button>
                    @if ($form['specialDaySetting'] > 1)
                        <button type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="حذف آخرین روز"
                            wire:click="removeFormCounter('specialDaySetting')" class="btn btn-outline-danger btn-sm">
                            <span wire:loading.remove wire:target="removeFormCounter('specialDaySetting')">
                                <i class="fa fa-minus me-1" aria-hidden="true"></i> حذف روز
                            </span>
                            <span wire:loading wire:target="removeFormCounter('specialDaySetting')">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </span>
                        </button>
                    @endif
                </div>
            </div>

            @for ($i = 0; $i < $form['specialDaySetting']; $i++)
                <div class="sd-day">
                    <div class="sd-day-head">
                        <span class="sd-day-badge">{{ $i + 1 }}</span>
                        <div class="sd-day-date">
                            <label class="sd-label" for="special-date-{{ $i }}">تاریخ روز خاص</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                                <input type="text" id="special-date-{{ $i }}"
                                    wire:model='form.specialDaydateValues.{{ $i }}' data-jdp
                                    data-name="form.specialDaydateValues.{{ $i }}" data-id="{{ $i }}"
                                    class="form-control specialDate" placeholder="انتخاب تاریخ" autocomplete="off"
                                    dir="ltr">
                            </div>
                        </div>
                    </div>
                    <p class="sd-note">
                        <i class="fa fa-lightbulb-o me-1" aria-hidden="true"></i>
                        در تاریخ انتخابی، به‌جای برنامه‌ی هفتگی، فقط بازه‌های زمانی زیر برای نوبت‌دهی فعال است.
                    </p>

                    <div class="sd-ranges-head">
                        <span class="sd-label mb-0"><i class="fa fa-clock-o me-1" aria-hidden="true"></i> بازه‌های زمانی</span>
                        <div class="sd-toolbar-actions">
                            <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                title="اضافه کردن بازه ی زمانی"
                                wire:click="addspecialDayTimeCounter('specialTimeCounter','{{ $i }}')"
                                class="btn btn-sm btn-info rounded-pill">
                                <span wire:loading.remove
                                    wire:target="addspecialDayTimeCounter('specialTimeCounter',{{ $i }})">
                                    <i class="fa fa-plus-circle me-1" aria-hidden="true"></i> اضافه کردن ساعت
                                </span>
                                <span wire:loading
                                    wire:target="addspecialDayTimeCounter('specialTimeCounter',{{ $i }})">
                                    <span class="spinner-border spinner-border-sm" role="status"></span>
                                </span>
                            </button>
                            @if ($form['specialTimeCounter'][$i] > 1)
                                <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="حذف کردن بازه ی زمانی"
                                    wire:click="removespecialDayTimeCounter('specialTimeCounter',{{ $i }})"
                                    class="btn btn-sm btn-outline-danger rounded-pill">
                                    <span wire:loading.remove
                                        wire:target="removespecialDayTimeCounter('specialTimeCounter',{{ $i }})">
                                        <i class="fa fa-minus me-1" aria-hidden="true"></i> حذف بازه
                                    </span>
                                    <span wire:loading
                                        wire:target="removespecialDayTimeCounter('specialTimeCounter',{{ $i }})">
                                        <span class="spinner-border spinner-border-sm" role="status"></span>
                                    </span>
                                </button>
                            @endif
                        </div>
                    </div>

                    @for ($j = $form['timeitrator'][$i]; $j < $form['specialTimeCounter'][$i]; $j++)
                        <div class="sd-range">
                            <div class="sd-range-field">
                                <label for="input-time-s-{{ $i }}-{{ $j }}" class="sd-label">از ساعت</label>
                                @include('appointmentsetting::components.generalsetting.time24', ['model' => "form.specialDaytimeValues.{$i}.{$j}.start", 'id' => "input-time-s-{$i}-{$j}"])
                            </div>
                            <i class="fa fa-long-arrow-left sd-range-arrow" aria-hidden="true"></i>
                            <div class="sd-range-field">
                                <label for="input-time-e-{{ $i }}-{{ $j }}" class="sd-label">تا ساعت</label>
                                @include('appointmentsetting::components.generalsetting.time24', ['model' => "form.specialDaytimeValues.{$i}.{$j}.end", 'id' => "input-time-e-{$i}-{$j}"])
                            </div>
                        </div>
                    @endfor
                </div>
            @endfor
        </div>
    </div>

    <style>
        .sd-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .75rem 1rem;
            margin-bottom: 1rem;
            background: #f5f7fb;
            border: 1px solid #e9edf4;
            border-radius: 10px;
        }

        .sd-toolbar-text {
            flex: 1 1 280px;
            display: flex;
            gap: .5rem;
            align-items: flex-start;
            color: #5b6477;
            font-size: .9rem;
            line-height: 1.8;
        }

        .sd-toolbar-text i {
            margin-top: .45rem;
            color: var(--primary-bg-color, #6259ca);
        }

        .sd-toolbar-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .sd-day {
            margin-bottom: 1rem;
            padding: 1rem 1.1rem;
            background: #fff;
            border: 1px solid #e9edf4;
            border-inline-start: 4px solid var(--primary-bg-color, #6259ca);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, .04);
        }

        .sd-day-head {
            display: flex;
            align-items: flex-end;
            gap: .85rem;
        }

        .sd-day-badge {
            flex: 0 0 auto;
            width: 2.2rem;
            height: 2.2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 700;
            color: #fff;
            background: var(--primary-bg-color, #6259ca);
            margin-bottom: .2rem;
        }

        .sd-day-date {
            flex: 1 1 auto;
            max-width: 340px;
        }

        .sd-label {
            display: block;
            margin-bottom: .35rem;
            font-weight: 600;
            color: #16213a;
        }

        .sd-note {
            margin: .75rem 0 1rem;
            padding: .5rem .75rem;
            color: #5b6477;
            background: #f5f7fb;
            border-radius: 8px;
            font-size: .88rem !important;
        }

        .sd-ranges-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            padding-top: .85rem;
            margin-bottom: .75rem;
            border-top: 1px dashed #dfe4ee;
        }

        .sd-range {
            display: flex;
            align-items: flex-end;
            gap: .75rem;
            margin-bottom: .75rem;
            padding: .75rem;
            background: #fafbfd;
            border: 1px solid #eef1f6;
            border-radius: 8px;
        }

        .sd-range-field {
            flex: 1 1 0;
            min-width: 0;
        }

        .sd-range-arrow {
            flex: 0 0 auto;
            margin-bottom: .75rem;
            color: #98a2b8;
        }

        @media (max-width: 575px) {
            .sd-day-head {
                align-items: flex-start;
            }

            .sd-range {
                flex-direction: column;
                align-items: stretch;
            }

            .sd-range-arrow {
                display: none;
            }
        }

        .dark-mode .sd-toolbar,
        .dark-mode .sd-note,
        .dark-mode .sd-range {
            background: #232a3d;
            border-color: #323b52;
            color: #a9b1c4;
        }

        .dark-mode .sd-day {
            background: #1c2233;
            border-color: #323b52;
        }

        .dark-mode .sd-label {
            color: #eef1f8;
        }

        .dark-mode .sd-ranges-head {
            border-top-color: #323b52;
        }
    </style>
</div>
