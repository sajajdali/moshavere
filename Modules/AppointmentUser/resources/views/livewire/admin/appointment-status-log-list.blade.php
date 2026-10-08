<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">تاریخچه تغییر وضعیت نوبت‌ها</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">خانه</a></li>
                <li class="breadcrumb-item active" aria-current="page">تاریخچه تغییر وضعیت نوبت‌ها</li>
            </ol>
        </div>
    </div>

    <div class="card custom-card">
        <div class="card-header border-bottom">
            <h3 class="card-title">فهرست تغییرات</h3>
        </div>
        <div class="card-body">
            <style>
                .sl-filters { background: #f7f9fc; border: 1px solid #e6ebf3; border-radius: 14px; padding: 16px 16px 6px; margin-bottom: 20px; }
                .sl-filters__head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 14px; }
                .sl-filters__title { font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 8px; }
                .sl-filters__count { background: #4f46e5; color: #fff; border-radius: 999px; font-size: 11px; min-width: 20px; height: 20px; padding: 0 6px; display: inline-grid; place-items: center; }
                .sl-field { margin-bottom: 14px; }
                .sl-field label { display: block; font-size: 12px; font-weight: 600; color: #5b6b7f; margin-bottom: 6px; }
                .sl-field .input-group-text { background: #fff; border-color: #dfe5ee; color: #8593a6; }
                .sl-field .form-control { border-color: #dfe5ee; background: #fff; min-height: 40px; }
                .sl-field .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
                .sl-range { display: flex; align-items: center; gap: 8px; }
                .sl-range span { font-size: 12px; color: #8593a6; flex: 0 0 auto; }
                .sl-range .input-group { flex: 1 1 0; min-width: 0; }
                .sl-section { font-size: 11px; font-weight: 700; color: #8593a6; letter-spacing: 0; margin: 2px 0 10px; display: flex; align-items: center; gap: 8px; }
                .sl-section::after { content: ''; flex: 1; height: 1px; background: #e6ebf3; }
            </style>

            @php
                $activeFilters = collect([$search, $event, $toStatus, $patient, $doctorId, $changedBy, $dateFrom, $dateTo])
                    ->filter(fn ($v) => trim((string) $v) !== '')->count();
            @endphp

            <div class="sl-filters" x-data="{ open: {{ $activeFilters ? 'true' : 'false' }} }">
                <div class="sl-filters__head" :style="open ? '' : 'margin-bottom:10px'">
                    <span class="sl-filters__title" role="button" tabindex="0" style="cursor:pointer;flex:1 1 auto"
                        @click="open = !open" @keydown.enter="open = !open">
                        <i class="fa" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" style="font-size:11px"></i>
                        <i class="fa fa-filter"></i> فیلترها
                        @if ($activeFilters)
                            <span class="sl-filters__count">{{ $activeFilters }}</span>
                        @endif
                    </span>
                    @if ($activeFilters)
                        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="resetFilters">
                            <i class="fa fa-times me-1"></i> پاک کردن فیلترها
                        </button>
                    @endif
                </div>

                <div x-show="open" x-transition.opacity.duration.150ms>
                <div class="sl-section">جست‌وجو و نوع تغییر</div>
                <div class="row">
                    <div class="col-lg-6 sl-field">
                        <label for="log-search">جست‌وجوی کلی</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                            <input id="log-search" type="search" class="form-control" wire:model.live.debounce.300ms="search"
                                placeholder="کد پیگیری، شناسه نوبت، نام یا موبایل…">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 sl-field">
                        <label for="log-event">نوع رویداد</label>
                        <select id="log-event" class="form-control" wire:model.live="event">
                            <option value="">همه رویدادها</option>
                            @foreach ($events as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 sl-field">
                        <label for="log-status">وضعیت جدید</label>
                        <select id="log-status" class="form-control" wire:model.live="toStatus">
                            <option value="">همه وضعیت‌ها</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}">{{ $status->getName() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="sl-section">افراد و زمان نوبت</div>
                <div class="row">
                    <div class="col-lg-4 col-md-6 sl-field">
                        <label for="log-patient">بیمار</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-user"></i></span>
                            <input id="log-patient" type="search" class="form-control" wire:model.live.debounce.300ms="patient" placeholder="نام یا موبایل بیمار">
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 sl-field">
                        <label for="log-doctor">پزشک</label>
                        <select id="log-doctor" class="form-control" wire:model.live="doctorId">
                            <option value="">همه پزشکان</option>
                            @foreach ($doctors as $doctor)
                                <option value="{{ $doctor->doctor_id }}">{{ $doctor->doctor_name ?? ('#'.$doctor->doctor_id) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-6 sl-field">
                        <label for="log-changer">تغییر دهنده</label>
                        <select id="log-changer" class="form-control" wire:model.live="changedBy">
                            <option value="">همه</option>
                            @foreach ($changers as $name)
                                <option value="{{ $name }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-6 sl-field">
                        <label>تاریخ نوبت (از - تا)</label>
                        <div class="sl-range">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                <input data-jdp data-name="dateFrom" value="{{ $dateFrom }}" class="form-control" placeholder="از تاریخ" type="text" autocomplete="off" readonly>
                            </div>
                            <span>تا</span>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                <input data-jdp data-name="dateTo" value="{{ $dateTo }}" class="form-control" placeholder="تا تاریخ" type="text" autocomplete="off" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>

            <div class="table-responsive mb-3">
                <table class="table text-nowrap text-md-nowrap table-bordered text-center" wire:loading.class="op-0-3">
                    <thead>
                        <tr class="table-primary">
                            <th>#</th>
                            <th>زمان تغییر</th>
                            <th>رویداد</th>
                            <th>از وضعیت</th>
                            <th>به وضعیت</th>
                            <th>تغییر دهنده</th>
                            <th>کد پیگیری</th>
                            <th>بیمار</th>
                            <th>پزشک</th>
                            <th>زمان نوبت</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td dir="ltr">{{ verta($log->created_at)->format('Y/m/d H:i:s') }}</td>
                                <td>
                                    <span class="badge {{ $log->event === 'deleted' ? 'bg-danger' : ($log->event === 'created' ? 'bg-success' : 'bg-info') }}">
                                        {{ $events[$log->event] ?? $log->event }}
                                    </span>
                                </td>
                                <td>
                                    @if ($log->from_status)
                                        <span class="badge {{ $log->from_status->getBadgeColor() }}">{{ $log->from_status->getName() }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if ($log->to_status)
                                        <span class="badge {{ $log->to_status->getBadgeColor() }}">{{ $log->to_status->getName() }}</span>
                                    @elseif ($log->event === 'deleted')
                                        <span class="badge bg-danger">حذف شد</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if ($log->changed_by_name)
                                        {{ $log->changed_by_name }}
                                    @else
                                        <span class="text-muted">
                                            {{ match ($log->source) { 'console' => 'سیستم (زمان‌بندی/کنسول)', 'api' => 'API', default => 'سیستم / بیمار (بدون ورود)' } }}
                                        </span>
                                    @endif
                                    @if ($log->ip)
                                        <div class="small text-muted" dir="ltr">{{ $log->ip }}</div>
                                    @endif
                                </td>
                                <td>{{ $log->tracking_code ?? '—' }} <div class="small text-muted">نوبت #{{ $log->appointment_user_id }}</div></td>
                                <td>
                                    {{ $log->patient_name ?? '—' }}
                                    @if ($log->patient_mobile)
                                        <div class="small text-muted" dir="ltr">{{ $log->patient_mobile }}</div>
                                    @endif
                                </td>
                                <td>{{ $log->doctor_name ?? '—' }}</td>
                                <td dir="ltr">
                                    @if ($log->date_visit)
                                        {{ verta($log->date_visit)->format('Y/m/d') }} {{ $log->start_time ? substr($log->start_time, 0, 5) : '' }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10"><div class="alert alert-info mb-0">تغییری ثبت نشده است.</div></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $logs->links() }}
        </div>
    </div>

    <script>
        (function () {
            function initPicker() {
                if (!window.jalaliDatepicker) { return setTimeout(initPicker, 100); }
                jalaliDatepicker.startWatch({ zIndex: 99999, autoHide: true, hideAfterChange: true });
            }
            initPicker();

            // رویداد روی document تا بعد از رندر مجدد Livewire هم کار کند
            if (!window.__statusLogDateBound) {
                window.__statusLogDateBound = true;
                ['input', 'change'].forEach(function (ev) {
                    document.addEventListener(ev, function (e) {
                        var el = e.target;
                        if (!el.matches || !el.matches('[data-jdp][data-name]')) { return; }
                        var root = el.closest('[wire\\:id]');
                        if (root && window.Livewire) {
                            Livewire.find(root.getAttribute('wire:id')).set(el.dataset.name, el.value);
                        }
                    });
                });
            }
        })();
    </script>
</div>


