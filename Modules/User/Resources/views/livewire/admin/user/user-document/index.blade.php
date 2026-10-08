<div class="pf-page" x-data="{ tab: (location.hash || '#appointments').replace('#', '') }"
    x-init="if (!['appointments','feedbacks','payments','finance','notes'].includes(tab)) tab = 'appointments'">
    @php
        $patientName = trim((string) $user->fullName) !== '' ? $user->fullName : $user->mobile;
        $initial = mb_substr(trim((string) $patientName), 0, 1);
        $feedbackCount = $answeredForms->count() + $legacyFeedbacks->count();
        $allFeedbacks = $answeredForms->map(fn ($item) => $item + ['title' => $item['form']])
            ->concat($legacyFeedbacks->map(fn ($item) => $item + ['title' => 'نظرسنجی پس از ویزیت']))
            ->sortByDesc('answered_at')->values();
    @endphp

    @once
        @push('styles')
            <style>
                .pf-page { --pf-border: #e6e9ef; --pf-muted: #6b7a8c; --pf-soft: #f4f7fb; --pf-accent: #2f80c9; }
                .pf-card { background: #fff; border: 1px solid var(--pf-border); border-radius: 14px; box-shadow: 0 1px 3px rgba(16, 24, 40, .05); margin-bottom: 20px; }
                .pf-hero { padding: 22px 24px; }
                .pf-hero-top { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; }
                .pf-avatar { flex: 0 0 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #2f80c9, #5ab0e8); color: #fff;
                    display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 700; }
                .pf-hero-main { flex: 1; min-width: 220px; }
                .pf-name { font-size: 20px; font-weight: 700; margin: 0 0 4px; color: inherit; }
                .pf-sub { color: var(--pf-muted); font-size: 13px; }
                .pf-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
                .pf-chip { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; font-size: 13px; border-radius: 8px;
                    background: var(--pf-soft); border: 1px solid var(--pf-border); }
                .pf-chip i { color: var(--pf-muted); }
                .pf-chip .pf-chip-label { color: var(--pf-muted); }
                .pf-chip.is-empty { opacity: .6; }
                .pf-ltr { direction: ltr; unicode-bidi: isolate; display: inline-block; }
                .pf-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 20px; padding-top: 20px; border-top: 1px dashed var(--pf-border); }
                .pf-stat { display: flex; align-items: center; gap: 12px; }
                .pf-stat-icon { flex: 0 0 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 17px; }
                .pf-stat-icon.blue { background: #e3f0fb; color: #2f80c9; }
                .pf-stat-icon.amber { background: #fdf0d3; color: #c98a0c; }
                .pf-stat-icon.green { background: #dcf3e6; color: #1f9d55; }
                .pf-stat-value { font-size: 20px; font-weight: 700; line-height: 1.2; }
                .pf-stat-label { font-size: 12px; color: var(--pf-muted); }
                .pf-form { margin-top: 22px; padding-top: 22px; border-top: 1px dashed var(--pf-border); }
                .pf-form-section { background: var(--pf-soft); border: 1px solid var(--pf-border); border-radius: 12px; padding: 16px 18px 18px; margin-bottom: 14px; }
                .pf-form-section-title { display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 14px; margin-bottom: 14px; color: var(--pf-accent); }
                .pf-form-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; }
                .pf-input .input-group-text { background: #fff; color: var(--pf-muted); border-color: var(--pf-border); }
                .pf-input .form-control { border-color: var(--pf-border); }
                .pf-input:focus-within .input-group-text, .pf-input:focus-within .form-control { border-color: var(--pf-accent); }
                .pf-input.is-invalid .input-group-text, .pf-input.is-invalid .form-control { border-color: #dc3545; }
                .pf-form-actions { display: flex; gap: 8px; margin-top: 18px; }
                .pf-tabs { display: flex; gap: 4px; padding: 8px 12px 0; border-bottom: 1px solid var(--pf-border); overflow-x: auto; }
                .pf-tab { display: inline-flex; align-items: center; gap: 8px; padding: 12px 16px; border: 0; background: none; color: var(--pf-muted);
                    font-weight: 600; font-size: 14px; white-space: nowrap; border-bottom: 3px solid transparent; margin-bottom: -1px; cursor: pointer; }
                .pf-tab:hover { color: var(--pf-accent); }
                .pf-tab.is-active { color: var(--pf-accent); border-bottom-color: var(--pf-accent); }
                .pf-tab-count { font-size: 11px; font-weight: 700; padding: 1px 8px; border-radius: 20px; background: var(--pf-soft); color: var(--pf-muted); }
                .pf-tab.is-active .pf-tab-count { background: #e3f0fb; color: var(--pf-accent); }
                .pf-panel { padding: 20px 22px; }
                .pf-table th { font-size: 12px; font-weight: 600; color: var(--pf-muted); background: var(--pf-soft); white-space: nowrap; }
                .pf-table td { vertical-align: middle; font-size: 13.5px; }
                .pf-table .pf-id { color: var(--pf-muted); font-size: 12px; }
                .pf-empty { text-align: center; padding: 40px 10px; color: var(--pf-muted); }
                .pf-empty i { font-size: 34px; opacity: .5; display: block; margin-bottom: 10px; }
                .pf-feedback { border: 1px solid var(--pf-border); border-radius: 12px; margin-bottom: 10px; background: #fff; }
                .pf-feedback > summary { display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap; padding: 14px 16px; cursor: pointer; list-style: none; }
                .pf-feedback > summary::-webkit-details-marker { display: none; }
                .pf-feedback[open] > summary { border-bottom: 1px solid var(--pf-border); background: var(--pf-soft); border-radius: 12px 12px 0 0; }
                .pf-feedback-body { padding: 14px 16px; display: grid; gap: 12px; }
                .pf-qa-q { font-size: 12px; color: var(--pf-muted); margin-bottom: 2px; }
                .pf-qa-a { font-weight: 600; }
                .pf-note { display: flex; gap: 12px; align-items: flex-start; justify-content: space-between; padding: 14px 16px; border: 1px solid var(--pf-border);
                    border-radius: 12px; margin-bottom: 10px; background: #fffdf5; }
                .pf-note-date { font-size: 12px; color: var(--pf-muted); margin-top: 6px; }
                .pf-soon { text-align: center; padding: 48px 10px; color: var(--pf-muted); }
                .pf-soon i { font-size: 38px; opacity: .45; display: block; margin-bottom: 12px; }
                .dark-mode .pf-page { --pf-border: #2f3f55; --pf-muted: #a9b6c6; --pf-soft: #1f2a3a; }
                .dark-mode .pf-card, .dark-mode .pf-feedback { background: #17202e; }
                .dark-mode .pf-input .input-group-text { background: #1f2a3a; }
                .dark-mode .pf-note { background: #2e2818; }
                @media (max-width: 640px) { .pf-stats { grid-template-columns: 1fr; } .pf-hero { padding: 18px 16px; } }
            </style>
        @endpush
    @endonce

    <div class="page-header">
        <div>
            <h1 class="page-title">پرونده بیمار</h1>
        </div>
        <div class="ms-auto pageheader-btn d-flex gap-2">
            <a href="{{ route('admin.user.report', $user) }}" class="btn btn-secondary">
                <i class="fa fa-bar-chart me-1" aria-hidden="true"></i> گزارش جامع کاربر
            </a>
        </div>
    </div>

    @include('admin::layouts.components.alert')

    {{-- patient header --}}
    <div class="pf-card pf-hero">
        <div class="pf-hero-top">
            <div class="pf-avatar" aria-hidden="true">{{ $initial }}</div>
            <div class="pf-hero-main">
                <h2 class="pf-name">{{ $patientName }}</h2>
                <div class="pf-sub">
                    @if (filled($user->document_number))
                        شماره پرونده {{ $user->document_number }}
                    @else
                        شماره پرونده ثبت نشده
                    @endif
                </div>
            </div>
            @can('update', $user)
                @if (!$editingProfile)
                    <button type="button" class="btn btn-primary" wire:click="editProfile">
                        <i class="fa fa-pencil me-1" aria-hidden="true"></i> ویرایش اطلاعات
                    </button>
                @endif
            @endcan
        </div>

        @if ($editingProfile)
            <form class="pf-form" wire:submit.prevent="saveProfile">
                @foreach ([
                    ['اطلاعات هویتی', 'fa-id-card-o', [
                        ['first_name', 'نام', 'text', 'fa-user-o', null, false],
                        ['last_name', 'نام خانوادگی', 'text', 'fa-user-o', null, false],
                        ['national_code', 'کد ملی', 'text', 'fa-id-card-o', 'ده رقم، بدون فاصله', false],
                    ]],
                    ['اطلاعات تماس', 'fa-phone', [
                        ['mobile', 'موبایل', 'tel', 'fa-mobile', 'مثال: 09123456789', true],
                        ['email', 'ایمیل', 'email', 'fa-envelope-o', 'اختیاری', false],
                    ]],
                    ['اطلاعات پرونده', 'fa-folder-open-o', [
                        ['document_number', 'شماره پرونده', 'text', 'fa-hashtag', 'اختیاری', false],
                    ]],
                ] as [$groupTitle, $groupIcon, $groupFields])
                    <div class="pf-form-section">
                        <div class="pf-form-section-title"><i class="fa {{ $groupIcon }}" aria-hidden="true"></i>{{ $groupTitle }}</div>
                        <div class="pf-form-grid">
                            @foreach ($groupFields as [$field, $label, $type, $icon, $hint, $required])
                                <div>
                                    <label class="form-label" for="profile-{{ $field }}">{{ $label }}@if ($required)<span class="text-danger"> *</span>@endif</label>
                                    <div class="input-group pf-input @error('profile.' . $field) is-invalid @enderror">
                                        <span class="input-group-text"><i class="fa {{ $icon }}" aria-hidden="true"></i></span>
                                        <input id="profile-{{ $field }}" type="{{ $type }}" wire:model="profile.{{ $field }}"
                                            class="form-control @error('profile.' . $field) is-invalid @enderror"
                                            @if (in_array($field, ['mobile', 'national_code', 'email'])) dir="ltr" style="text-align: left" @endif
                                            @if ($hint) placeholder="{{ $hint }}" @endif>
                                    </div>
                                    @error('profile.' . $field)
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <div class="pf-form-actions">
                    <button type="submit" class="btn btn-success" wire:loading.attr="disabled" wire:target="saveProfile">
                        <span wire:loading.remove wire:target="saveProfile"><i class="fa fa-check me-1" aria-hidden="true"></i> ذخیره تغییرات</span>
                        <span wire:loading wire:target="saveProfile"><span class="spinner-border spinner-border-sm me-1" role="status"></span> در حال ذخیره...</span>
                    </button>
                    <button type="button" class="btn btn-light" wire:click="cancelEditProfile">انصراف</button>
                </div>
            </form>
        @else
            <div class="pf-chips">
                @foreach ([
                    ['fa-mobile', 'موبایل', $user->mobile, true],
                    ['fa-id-card-o', 'کد ملی', $user->national_code, true],
                    ['fa-envelope-o', 'ایمیل', $user->email, true],
                ] as [$icon, $label, $value, $ltr])
                    <span class="pf-chip {{ filled($value) ? '' : 'is-empty' }}">
                        <i class="fa {{ $icon }}" aria-hidden="true"></i>
                        <span class="pf-chip-label">{{ $label }}:</span>
                        <strong @class(['pf-ltr' => $ltr && filled($value)])>{{ filled($value) ? $value : 'ثبت نشده' }}</strong>
                    </span>
                @endforeach
            </div>
        @endif

        <div class="pf-stats">
            <div class="pf-stat">
                <span class="pf-stat-icon blue"><i class="fa fa-calendar-check-o" aria-hidden="true"></i></span>
                <div>
                    <div class="pf-stat-value">{{ number_format($appointmentCount) }}</div>
                    <div class="pf-stat-label">تعداد نوبت ها</div>
                </div>
            </div>
            <div class="pf-stat">
                <span class="pf-stat-icon amber"><i class="fa fa-star-o" aria-hidden="true"></i></span>
                <div>
                    <div class="pf-stat-value">{{ number_format($feedbackCount) }}</div>
                    <div class="pf-stat-label">نظرسنجی های انجام شده</div>
                </div>
            </div>
            <div class="pf-stat">
                <span class="pf-stat-icon green"><i class="fa fa-credit-card" aria-hidden="true"></i></span>
                <div>
                    <div class="pf-stat-value">{{ number_format($paidTotal) }}</div>
                    <div class="pf-stat-label">مجموع پرداخت های موفق</div>
                </div>
            </div>
        </div>
    </div>

    {{-- tabs --}}
    <div class="pf-card">
        <div class="pf-tabs" role="tablist">
            <button type="button" class="pf-tab" :class="{ 'is-active': tab === 'appointments' }"
                @click="tab = 'appointments'; location.hash = 'appointments'">
                <i class="fa fa-bookmark" aria-hidden="true"></i> نوبت ها
                <span class="pf-tab-count">{{ number_format($appointmentCount) }}</span>
            </button>
            <button type="button" class="pf-tab" :class="{ 'is-active': tab === 'feedbacks' }"
                @click="tab = 'feedbacks'; location.hash = 'feedbacks'">
                <i class="fa fa-star-o" aria-hidden="true"></i> نظرسنجی ها
                <span class="pf-tab-count">{{ number_format($feedbackCount) }}</span>
            </button>
            <button type="button" class="pf-tab" :class="{ 'is-active': tab === 'payments' }"
                @click="tab = 'payments'; location.hash = 'payments'">
                <i class="fa fa-credit-card" aria-hidden="true"></i> پرداخت ها
                <span class="pf-tab-count">{{ number_format($payments->total()) }}</span>
            </button>
            <button type="button" class="pf-tab" :class="{ 'is-active': tab === 'finance' }"
                @click="tab = 'finance'; location.hash = 'finance'">
                <i class="fa fa-line-chart" aria-hidden="true"></i> گزارش های مالی
            </button>
            <button type="button" class="pf-tab" :class="{ 'is-active': tab === 'notes' }"
                @click="tab = 'notes'; location.hash = 'notes'">
                <i class="fa fa-comment-o" aria-hidden="true"></i> یادداشت ها
                <span class="pf-tab-count">{{ number_format($notes->count()) }}</span>
            </button>
        </div>

        {{-- appointments --}}
        <div class="pf-panel" x-show="tab === 'appointments'" wire:loading.class="op-0-3" wire:target="appointments_page">
            @if ($appointments->isEmpty())
                <div class="pf-empty"><i class="fa fa-calendar-o" aria-hidden="true"></i>برای این بیمار نوبتی ثبت نشده است</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover pf-table text-nowrap text-center mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>تاریخ</th>
                                <th>ساعت</th>
                                <th>پزشک</th>
                                <th>بخش</th>
                                <th>مطب</th>
                                <th>نوع</th>
                                <th>وضعیت</th>
                                <th>پرداخت</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($appointments as $appointment)
                                <tr wire:key="appointment-{{ $appointment->id }}">
                                    <td class="pf-id">{{ $appointment->id }}</td>
                                    <td class="fw-semibold">{{ verta($appointment->date_visit)->format('Y/m/d') }}</td>
                                    <td dir="ltr">
                                        @if ($appointment->start_time)
                                            {{ substr($appointment->start_time, 0, 5) }}@if ($appointment->end_time) – {{ substr($appointment->end_time, 0, 5) }}@endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $appointment->doctor?->fullName ?: '-' }}</td>
                                    <td>{{ $appointment->service?->title ?? '-' }}</td>
                                    <td>{{ $appointment->place?->title ?? '-' }}</td>
                                    <td>
                                        {{ $appointment->kind->getName() }}
                                        @if ($appointment->isOnline() && $appointment->online->first())
                                            <a class="badge bg-warning text-dark ms-1"
                                                href="{{ route('admin.appointment_user.message.detail', ['onlineAppId' => $appointment->online->first()->id]) }}">چت</a>
                                        @endif
                                    </td>
                                    <td><span class="badge {{ $appointment->status->getBadgeColor() }}">{{ $appointment->status->getName() }}</span></td>
                                    <td>
                                        @if ($appointment->transaction)
                                            @if ($appointment->transaction->paid_by === \Modules\Transaction\Enum\TransactionPaidEnum::NO_NEED_TO_PAY)
                                                <span class="text-muted">بدون نیاز به پرداخت</span>
                                            @else
                                                <span class="badge {{ $appointment->transaction->status->badgeClass() }}">{{ $appointment->transaction->status->getName() }}</span>
                                                <span class="ms-1">{{ number_format($appointment->transaction->total_cost) }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $appointments->links() }}</div>
            @endif
        </div>

        {{-- feedbacks --}}
        <div class="pf-panel" x-show="tab === 'feedbacks'" x-cloak>
            @forelse ($allFeedbacks as $feedback)
                <details class="pf-feedback" wire:key="feedback-{{ $loop->index }}">
                    <summary>
                        <span class="fw-semibold"><i class="fa fa-star-o me-1 text-warning" aria-hidden="true"></i>{{ $feedback['title'] }}</span>
                        <span class="pf-sub">
                            نوبت <bdi>#{{ $feedback['appointment_id'] }}</bdi>
                            · <bdi>{{ verta($feedback['answered_at'])->format('Y/m/d H:i') }}</bdi>
                        </span>
                    </summary>
                    <div class="pf-feedback-body">
                        @foreach ($feedback['answers'] as $item)
                            <div>
                                <div class="pf-qa-q">{{ $item['question'] }}</div>
                                <div class="pf-qa-a">{{ filled($item['answer']) ? $item['answer'] : '-' }}</div>
                            </div>
                        @endforeach
                    </div>
                </details>
            @empty
                <div class="pf-empty"><i class="fa fa-star-o" aria-hidden="true"></i>این بیمار هنوز به نظرسنجی ای پاسخ نداده است</div>
            @endforelse
        </div>

        {{-- payments --}}
        <div class="pf-panel" x-show="tab === 'payments'" x-cloak wire:loading.class="op-0-3" wire:target="payments_page">
            @if ($payments->isEmpty())
                <div class="pf-empty"><i class="fa fa-credit-card" aria-hidden="true"></i>پرداختی برای این بیمار ثبت نشده است</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover pf-table text-nowrap text-center mb-0">
                        <thead>
                            <tr>
                                <th>تاریخ</th>
                                <th>کد تراکنش</th>
                                <th>مربوط به</th>
                                <th>روش پرداخت</th>
                                <th>مبلغ</th>
                                <th>تخفیف</th>
                                <th>مبلغ نهایی</th>
                                <th>وضعیت</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $payment)
                                <tr wire:key="payment-{{ $payment->id }}">
                                    <td>{{ verta($payment->created_at)->format('Y/m/d') }}
                                        <span class="pf-id" dir="ltr">{{ verta($payment->created_at)->format('H:i') }}</span></td>
                                    <td class="pf-id" dir="ltr">{{ $payment->transaction_code ?? '-' }}</td>
                                    <td>
                                        @if ($payment->transactionable instanceof \Modules\AppointmentUser\app\Models\AppointmentUser)
                                            نوبت <bdi>#{{ $payment->transactionable->id }}</bdi>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $payment->paid_by?->getName() ?: '-' }}</td>
                                    <td>{{ number_format($payment->cost) }}</td>
                                    <td>{{ number_format($payment->discount_amount) }}</td>
                                    <td class="fw-bold">{{ number_format($payment->total_cost) }}</td>
                                    <td><span class="badge {{ $payment->status->badgeClass() }}">{{ $payment->status->getName() }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $payments->links() }}</div>
            @endif
        </div>

        {{-- financial management of the patient (Finance module): the whole page lives inside this tab --}}
        <div class="pf-panel" x-show="tab === 'finance'" x-cloak>
            @if ($finance)
                @livewire(\Modules\Finance\Livewire\PatientFinance::class, ['user' => $user, 'embedded' => true], key('patient-finance-' . $user->id))
            @else
                <div class="pf-soon">
                    <i class="fa fa-line-chart" aria-hidden="true"></i>
                    برای مشاهدهٔ گزارش مالی بیمار به دسترسی «مدیریت مالی» نیاز دارید.
                </div>
            @endif
        </div>

        {{-- notes --}}
        <div class="pf-panel" x-show="tab === 'notes'" x-cloak>
            <div class="mb-4">
                <label class="form-label" for="validationTextarea">افزودن یادداشت</label>
                <textarea class="form-control @error('form.comment') is-invalid @enderror" rows="3" wire:model='form.comment'
                    id="validationTextarea" placeholder="متن یادداشت را وارد کنید"></textarea>
                @error('form.comment')
                    <div class="invalid-feedback d-block">وارد کردن متن الزامی میباشد!</div>
                @enderror
                <button wire:click='addComment' wire:target='addComment' wire:loading.class='btn-loading btn-gray'
                    class="btn btn-success mt-3"><i class="fa fa-plus me-1" aria-hidden="true"></i> افزودن یادداشت</button>
            </div>
            @forelse ($notes as $comment)
                <div class="pf-note">
                    <div>
                        <div style="white-space: pre-line">{{ trim($comment->body) }}</div>
                        <div class="pf-note-date"><i class="fa fa-clock-o me-1" aria-hidden="true"></i>{{ verta($comment->created_at)->format('Y/m/d ساعت H:i') }}@if ($comment->author)
                                · <i class="fa fa-user-o me-1" aria-hidden="true"></i>{{ $comment->author->fullName ?: $comment->author->mobile }}@endif</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger delete_confirm_alert" data-id="{{ $comment->id }}"
                        data-label="یادداشت"><i class="fa fa-trash" aria-hidden="true"></i></button>
                </div>
            @empty
                <div class="pf-empty"><i class="fa fa-comment-o" aria-hidden="true"></i>هنوز یادداشتی ثبت نشده است</div>
            @endforelse
        </div>
    </div>
</div>
@push('scripts')
    <script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/admin.sweetalert.js') }}"></script>

    <script>
        $(document).ready(function() {
            Livewire.on('showAlert', param => {
                showSwalSuccess(param.message)
            });
            Livewire.on('error', param => {
                showSwalError(param.message)
            });
        });
    </script>
@endpush
