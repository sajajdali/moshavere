@php
    $dayAppointments = $this->ShowListOfAppointmentForSpecificDay();
    $bookedCount = collect($dayAppointments)
        ->filter(fn($slot) => !$slot['status'] && !empty($slot['appointment_user_id']))
        ->count();
    $freeCount = count($dayAppointments) - $bookedCount;

    // multiple appointments in one slot: number each entry inside its from/until group
    $multiPerHour = $fetchData['appointmentSetting']->appointmentsPerHour() > 1;
    $slotGroupSize = [];
    $slotPosition = [];
    if ($multiPerHour) {
        foreach ($dayAppointments as $i => $slot) {
            $groupKey = $slot['from'] . '|' . $slot['until'];
            $slotGroupSize[$groupKey] = ($slotGroupSize[$groupKey] ?? 0) + 1;
            $slotPosition[$i] = $slotGroupSize[$groupKey];
        }
    }
@endphp

<div class="sd-page" dir="rtl">
@once
    @push('styles')
        <link href="{{ admin_asset('css/specific-day-appointment.css') }}" rel="stylesheet">
        <style>
            .sd-row-multi-next .sd-time-values { opacity: .45; }
            .sd-multi-badge {
                display: inline-block; margin-top: 4px; padding: 1px 8px; border-radius: 10px;
                font-size: 11px; white-space: nowrap; color: var(--primary-bg-color, #6259ca);
                background: rgba(98, 89, 202, .12);
            }
        </style>
    @endpush
@endonce

    <div class="sd-shell">
        @include('admin::layouts.components.alert')
        @include('admin::layouts.components.loading')

        <div class="sd-heading">
            <div class="sd-breadcrumb"><span>ثبت نوبت</span><span class="sep">/</span><span>انتخاب روز</span><span
                    class="sep">/</span><span class="current">برنامه‌ی روز</span></div>
            <h1 class="sd-title">
                {{ $edited['status'] ? 'تغییر زمان نوبت' : 'برنامه‌ی روز ' . verta($fetchData['selectedDate'])->format('l') }}
            </h1>
        </div>

        <div class="sd-docbar">
            <div class="sd-doctor">
                <span class="sd-doctor-icon"><i class="fa fa-user-md" aria-hidden="true"></i></span>
                <span class="sd-doctor-text">
                    <span class="sd-doctor-name">{{ $fetchData['doc']->speciality_type == 1 ? 'دکتر' : '' }}
                        {{ $fetchData['doc']->fullName }}</span>
                    <span class="sd-doctor-meta">{{ $fetchData['service']->title ?? '' }}</span>
                </span>
            </div>
            <div class="sd-actions">
                @if ($fetchData['showChangeServiceBtn'])
                    <button id="changeDocButton" class="sd-btn sd-btn-muted" type="button" data-bs-toggle="modal"
                        data-bs-target="#changeDocmodal">تغییر پزشک و بخش</button>
                @endif
                @if (!$edited['status'])
                    <button class="sd-btn sd-btn-primary" type="button" data-bs-toggle="modal"
                        wire:click="dateHasBeenChange" data-bs-target="#RegistrAnAppointment">ثبت نوبت</button>
                @endif
            </div>
        </div>

        @if ($edited['status'])
            <div class="sd-editing" role="status"><span class="sd-editing-dot"></span><span>در حال تغییر زمان نوبت
                    «{{ $edited['old_app']->user->fullName }}» هستید — ساعت مقصد را از فهرست انتخاب کنید.</span></div>
        @endif

        @if (!empty($fetchData['navigationMessage']))
            <div class="sd-navigation-message" role="status">{{ $fetchData['navigationMessage'] }}</div>
        @endif

        <section class="sd-schedule" aria-label="برنامه نوبت‌های روز">
            <div class="sd-toolbar">
                <div class="sd-date-nav">
                    <button type="button" class="sd-arrow" wire:click="previousDay" wire:loading.attr="disabled"
                        wire:target="previousDay,nextDay,form.changeDate" title="روز قبل">
                        <i class="fa fa-angle-left"
                            aria-hidden="true"></i></button>
                    <label class="sd-date-wrap" title="انتخاب تاریخ">
                        <i class="fa fa-calendar-o" aria-hidden="true"></i>
                        <input class="sd-date-input" type="text" id="currentDate" data-jdp
                            data-name="form.changeDate"
                            wire:key="appointment-date-{{ $fetchData['selectedDate']->format('Y-m-d') }}"
                            wire:model="form.changeDate" value="{{ $form['changeDate'] }}" aria-label="انتخاب تاریخ">
                    </label>
                    <button type="button" class="sd-arrow" wire:click="nextDay" wire:loading.attr="disabled"
                        wire:target="previousDay,nextDay,form.changeDate" title="روز بعد"><i class="fa fa-angle-right"
                            aria-hidden="true"></i></button>
                    <button type="button" class="sd-today" wire:click="today" wire:loading.attr="disabled"
                        wire:target="today">امروز</button>
                </div>
                <div class="sd-toolbar-side">
                    <span class="sd-stat"><span class="sd-dot sd-dot-free"></span><span>{{ $freeCount }}
                            آزاد</span></span>
                    <span class="sd-stat"><span class="sd-dot sd-dot-booked"></span><span>{{ $bookedCount }} رزرو
                            شده</span></span>
                    <span class="sd-stat"><span class="sd-dot sd-dot-all"></span><span>{{ count($dayAppointments) }} کل
                            نوبت</span></span>
                    <span class="sd-filters" aria-label="فیلتر نوبت‌ها">
                        <button type="button" class="sd-filter {{ !$edited['status'] ? 'active' : '' }}"
                            data-sd-filter="all">همه</button>
                        <button type="button" class="sd-filter {{ $edited['status'] ? 'active' : '' }}"
                            data-sd-filter="free">فقط آزاد</button>
                    </span>
                </div>
            </div>

            <div class="spinner-border text-primary position-absolute top-50 start-50 sd-loading" role="status"
                wire:loading wire:target="previousDay,nextDay,today,form.changeDate"></div>

            @if (!empty($dayAppointments))
                <div class="sd-presence">
                    <span class="sd-presence-mark"></span><span>شروع حضور:
                        {{ substr($dayAppointments[0]['from'], 0, -3) }}</span>
                    <span class="sd-presence-line"></span><span>پایان حضور:
                        {{ substr($dayAppointments[array_key_last($dayAppointments)]['until'], 0, -3) }}</span>
                </div>
                <div class="sd-rows" id="appointment_content"
                    wire:key="appointment-times-{{ $fetchData['selectedDate']->format('Y-m-d') }}">
                    @foreach ($dayAppointments as $key => $eachTime)
                        @php
                            $ap = null;
                            $user = null;
                            $groupSize = $multiPerHour ? $slotGroupSize[$eachTime['from'] . '|' . $eachTime['until']] : 1;
                            $inGroup = $groupSize > 1;
                            $multiClass = $inGroup ? 'sd-row-multi' . (($slotPosition[$key] ?? 1) > 1 ? ' sd-row-multi-next' : '') : '';
                            if (!$eachTime['status'] && !empty($eachTime['appointment_user_id'])) {
                                $ap = Modules\AppointmentUser\app\Models\AppointmentUser::find(
                                    $eachTime['appointment_user_id'],
                                );
                                $user = $ap?->user;
                            }
                        @endphp

                        @if ($ap)
                            <div class="sd-row sd-row-booked {{ $multiClass }} {{ $ap->type == Modules\AppointmentUser\Enum\AppointmentUserTypeEnum::BETWEEN_PATIENTS ? 'sd-row-between' : '' }}"
                                data-slot-state="booked" @if ($edited['status']) style="display:none" @endif>
                                <div class="sd-time"><span class="sd-index">{{ $key + 1 }}</span><span
                                        class="sd-time-values"><span
                                            class="sd-time-from">{{ substr($eachTime['from'], 0, -3) }}</span><span
                                            class="sd-time-until">{{ substr($eachTime['until'], 0, -3) }}</span></span>
                                    @if ($inGroup)
                                        <span class="sd-multi-badge">نوبت {{ $slotPosition[$key] }} از {{ $groupSize }}</span>
                                    @endif
                                </div>
                                <span class="sd-marker"></span>
                                <div class="sd-slot-content">
                                    <div class="sd-patient">
                                        <div class="sd-patient-top"><span
                                                class="sd-patient-name">{{ $user?->fullName }}</span><span
                                                class="sd-mobile">{{ $user?->mobile }}</span></div>
                                        <div class="sd-patient-meta">
                                            <span>{{ $ap->service->title ?? '(بخش حذف شده)' }} @if ($ap->hasSegment())
                                                    {{ $ap->segmentsNames() }}
                                                @endif
                                            </span>
                                            @if (data_get(
                                                    $ap->setting?->detail,
                                                    Modules\AppointmentSetting\app\Models\AppointmentSetting::OPERATORS .
                                                        '.' .
                                                        Modules\AppointmentSetting\app\Models\AppointmentSetting::STATUS,
                                                    false))
                                                <span
                                                    class="sd-chip">{{ $ap->operator?->full_name ?? 'بدون اپراتور' }}</span>
                                            @endif
                                            @if ($ap->type == Modules\AppointmentUser\Enum\AppointmentUserTypeEnum::BETWEEN_PATIENTS)
                                                <span
                                                    class="sd-chip sd-chip-between">{{ $ap->type->getName() }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="sd-row-actions">
                                        <span
                                            class="sd-status badge {{ $ap->status->getBadgeColor() }}">{{ $ap->status->getName() }}</span>
                                        <div class="dropdown">
                                            <button class="sd-menu-button" type="button"
                                                id="appointment-actions-{{ $ap->id }}"
                                                data-bs-toggle="dropdown" aria-expanded="false"
                                                aria-label="عملیات نوبت"><i class="fa fa-ellipsis-v"
                                                    aria-hidden="true"></i></button>
                                            <ul class="dropdown-menu"
                                                aria-labelledby="appointment-actions-{{ $ap->id }}">
                                                @can('update', $ap)
                                                    @include('appointmentuser::components.appointmentlist.operationbutton')
                                                @endcan
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="sd-row {{ $multiClass }}" data-slot-state="free">
                                <div class="sd-time"><span class="sd-index">{{ $key + 1 }}</span><span
                                        class="sd-time-values"><span
                                            class="sd-time-from">{{ substr($eachTime['from'], 0, -3) }}</span><span
                                            class="sd-time-until">{{ substr($eachTime['until'], 0, -3) }}</span></span>
                                    @if ($inGroup)
                                        <span class="sd-multi-badge">نوبت {{ $slotPosition[$key] }} از {{ $groupSize }}</span>
                                    @endif
                                </div>
                                <span class="sd-marker"></span>
                                <div class="sd-slot-content">
                                    <div class="sd-free">
                                        <span class="sd-free-label">آزاد</span>
                                        <span class="sd-free-actions">
                                            @if ($eachTime['gap'])
                                                <span class="sd-gap">کمتر از مدت ویزیت</span>
                                            @endif
                                            @if ($edited['status'])
                                                <button type="button" class="sd-book sd-book-edit"
                                                    wire:loading.attr="disabled"
                                                    wire:click='changeAppointmentDate("{{ $eachTime['from'] }}","{{ $eachTime['until'] }}")'>انتقال
                                                    به این ساعت</button>
                                            @else
                                                <button type="button" class="sd-book" wire:loading.attr="disabled"
                                                    wire:click='passTimeToRegisterAppointmentModal("{{ $eachTime['from'] }}","{{ $eachTime['until'] }}")'>ثبت
                                                    نوبت</button>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    @if ($freeCount === 0)
                        <div class="sd-filter-empty" data-filter-empty @if (!$edited['status']) style="display:none" @endif>
                            <strong>نوبت آزادی برای این روز وجود ندارد</strong>
                            <span>برای مشاهده نوبت‌های ثبت‌شده فیلتر «همه»، یا برای انتخاب زمان آزاد روز دیگری را انتخاب کنید.</span>
                        </div>
                    @endif
                </div>
            @else
                <div class="sd-empty"><strong>زمان حضور برای این تاریخ تعیین نشده است</strong><span>با فلش‌های بالا روز
                        دیگری را ببینید یا تاریخ مورد نظر را از تقویم انتخاب کنید.</span></div>
            @endif
        </section>
    </div>
    <livewire:appointmentuser::admin.add-appointment.modal.service-and-doctor-modal :appId="$fetchData['appId']" :appTime="$fetchData['time']"
        :serviceId="$fetchData['service']->id" :placeId="$fetchData['place']" :segmentId="$fetchData['segment']"
        :key="'change-doctor-service-' . $fetchData['appId'] . '-' . $fetchData['service']->id . '-' . $fetchData['place']" />
    <livewire:appointmentuser::admin.add-appointment.modal.specific-day-appointment-registration-modal :appId="$fetchData['appId']"
        :appTime="$fetchData['time']" :appDate="verta($fetchData['selectedDate'])->format('Y-m-d')" :serviceId="$fetchData['service']->id" :placeId="$fetchData['place']" :segmentId="$fetchData['segment']" :key="'register-appointment-' .
            $fetchData['appId'] .
            '-' .
            $fetchData['service']->id .
            '-' .
            $fetchData['place']" />
    <div>
        @include('appointmentuser::components.appointmentlist.disapprovemodal')
    </div>
</div>
@push('scripts')
    <!-- SELECT2 JS -->
    <script src="{{ admin_asset('plugins/select2/select2.full.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/admin.sweetalert.js') }}"></script>
    <script src="{{ admin_asset('plugins/treeview/treeview.js') }}"></script>
    <script>
        $(document).ready(function() {
            var setAppModal = document.querySelector('#RegistrAnAppointment');
            var setAppModalInst = bootstrap.Modal.getOrCreateInstance(setAppModal);
            var myModalEl = document.querySelector('#changeDocmodal');
            var modal = bootstrap.Modal.getOrCreateInstance(myModalEl);

            function addJs() {
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
                $(document).off('input.appointmentDay', '#currentDate').on('input.appointmentDay', '#currentDate',
                    function() {
                        let selectedDate = $(this).val();
                        let seterValue = $(this).data('name');
                        @this.set(seterValue, selectedDate);
                    });
            };
            addJs();

            $(document).off('click.appointmentSlotFilter', '[data-sd-filter]')
                .on('click.appointmentSlotFilter', '[data-sd-filter]', function() {
                    const filter = $(this).data('sd-filter');
                    const schedule = $(this).closest('.sd-schedule');

                    schedule.find('[data-sd-filter]').removeClass('active');
                    $(this).addClass('active');
                    schedule.find('[data-slot-state="booked"]').toggle(filter === 'all');
                    schedule.find('[data-filter-empty]').toggle(filter === 'free');
                });

            Livewire.on('lunchRegisterModal', function() {
                setAppModalInst.show();
            });
            Livewire.on('lunchModal', function() {
                setTimeout(() => {
                    var myModal = new bootstrap.Modal(document.getElementById(
                        'resoanForDisapproveModal'), {
                        keyboard: false
                    });
                    myModal.show();
                }, 1000);
            });
            // the modal already received the date and time when it was mounted: open it right away,
            // without a server round trip (which showed the page loading overlay) or a fixed delay
            if ({{ $fetchData['showRegisterModal'] }}) {
                setAppModalInst.show();
            };
            Livewire.on('closeModal', function() {
                modal.hide();
                setAppModalInst.hide();
            });
            Livewire.on('urlDateChange', function(newDate) {
                var currentUrl = window.location.href;
                var baseUrl = currentUrl.split('/').slice(0, -1).join('/');
                var newUrl = baseUrl + '/' + newDate.newDate;
                window.history.pushState({
                    path: newUrl
                }, '', newUrl);
            })
            const payment_link = @json($fetchData['secretary_send_payment_link']);
            if (payment_link) {
                $('body').on('change', '.payment_pending_input', function() {
                    if ($(this).val() == 'false') {
                        $('#sendSubmitPaymentStatus').removeClass('d-none');
                    } else {
                        $('#sendSubmitPaymentStatus').addClass('d-none');
                    }
                });
            }
        });
    </script>
@endpush
