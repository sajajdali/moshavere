<div wire:keydown.escape.window="close">
    <div class="page-header">
        <div>
            <h1 class="page-title">نظرسنجی های انجام شده</h1>
        </div>
        <div class="ms-auto pageheader-btn d-flex gap-2">
            <a href="{{ route('admin.appointment.feedback.create') }}" class="btn btn-primary"><i class="fa fa-plus me-1"></i> ایجاد فرم نظرسنجی</a>
            <a href="{{ route('admin.appointment.feedback.forms') }}" class="btn btn-secondary">فرم های نظرسنجی</a>
        </div>
    </div>
    @include('admin::layouts.components.alert')

    <div class="card custom-card" wire:loading.class="op-0-3">
        <div class="card-header d-flex justify-content-between border-bottom">
            <h3 class="card-title">پاسخ های ثبت شده</h3>
            <div style="min-width: 240px">
                <select class="form-select" wire:model.live="formId">
                    <option value="">همه فرم ها</option>
                    @foreach ($allForms as $form)
                        <option value="{{ $form->id }}">{{ $form->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive mb-3">
                <table class="table table-sm table-bordered text-center align-middle">
                    <thead>
                        <tr class="table-primary">
                            <th>#</th>
                            <th>فرم</th>
                            <th>نام کاربر</th>
                            <th>نام پزشک</th>
                            <th>بخش</th>
                            <th>زمان ویزیت</th>
                            <th>ارسال لینک</th>
                            <th>زمان پاسخ</th>
                            <th>پاسخ ها</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($submissions as $row)
                            @php
                                $key = $row->appointment_user_id . '-' . $row->feedback_form_id;
                                $app = $appointments->get($row->appointment_user_id);
                                $sent = $links->get($row->appointment_user_id);
                            @endphp
                            <tr wire:key="row-{{ $key }}">
                                <td>{{ $submissions->firstItem() + $loop->index }}</td>
                                <td>{{ $forms->get($row->feedback_form_id)?->title ?? '-' }}</td>
                                <td>{{ $app?->user?->fullname ?? 'نوبت یافت نشد' }}</td>
                                <td>{{ $app?->doctor?->fullname ?? '-' }}</td>
                                <td>{{ $app?->service?->title ?? '-' }}</td>
                                <td>{{ $app?->date_visit ? verta($app->date_visit)->format('Y/m/d ساعت H:i') : '-' }}</td>
                                <td>{{ $sent ? verta($sent->created_at)->format('Y/m/d ساعت H:i') : '-' }}</td>
                                <td>{{ verta($row->answered_at)->format('Y/m/d ساعت H:i') }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="open('{{ $key }}')">
                                        <i class="fa fa-eye me-1"></i> مشاهده
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="alert alert-info mb-0">هیچ موردی یافت نشد</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">{{ $submissions->links() }}</div>
        </div>
    </div>

    @if ($modal)
        @php
            $mApp = $modal['appointment'];
            $mSent = $modal['link'];
            $answeredAt = $modal['answered_at'];
            $lag = $mSent && $answeredAt ? \Carbon\Carbon::parse($mSent->created_at)->diffForHumans(\Carbon\Carbon::parse($answeredAt), true) : null;
        @endphp
        <style>
            .fb-modal .fb-meta { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 10px; margin-bottom: 18px; }
            .fb-modal .fb-meta > div { background: #f7f9fc; border: 1px solid #e6ebf3; border-radius: 10px; padding: 10px 12px; }
            .fb-modal .fb-meta small { display: block; color: #7b8aa0; font-size: 11px; margin-bottom: 3px; }
            .fb-modal .fb-meta strong { font-size: 13px; }
            .fb-modal .fb-time { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 18px; }
            .fb-modal .fb-time > div { flex: 1 1 200px; border-radius: 10px; padding: 10px 14px; display: flex; align-items: center; gap: 10px; }
            .fb-modal .fb-time .sent { background: #eef5ff; color: #2b5fb3; }
            .fb-modal .fb-time .done { background: #e9f6ef; color: #1c7a4b; }
            .fb-modal .fb-time small { display: block; opacity: .8; font-size: 11px; }
            .fb-modal .fb-qa { border: 1px solid #e6ebf3; border-radius: 10px; padding: 12px 14px; margin-bottom: 10px; }
            .fb-modal .fb-qa .q { font-weight: 700; font-size: 14px; margin-bottom: 6px; }
            .fb-modal .fb-qa .a { white-space: pre-line; color: #3a4a5f; font-size: 14px; }
            .fb-modal .fb-qa .a.none { color: #9aa6b8; }
            .fb-modal .fb-qa .a.badges { white-space: normal; display: flex; flex-wrap: wrap; gap: 6px; }
            .fb-modal .fb-stars { color: #e0a82e; font-size: 20px; letter-spacing: 2px; }
            .fb-modal .fb-stars .off { color: #d6dde8; }
        </style>

        <div class="modal fade show d-block fb-modal" tabindex="-1" role="dialog" aria-modal="true"
            style="background: rgba(15, 23, 42, .55); overflow-y: auto;" wire:click.self="close">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa fa-comments-o me-1"></i>
                            {{ $modal['form']?->title ?? 'نظرسنجی' }}
                        </h5>
                        <button type="button" class="btn-close" aria-label="بستن" wire:click="close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="fb-time">
                            <div class="sent">
                                <i class="fa fa-paper-plane fa-lg"></i>
                                <span>
                                    <small>زمان ارسال لینک نظرسنجی</small>
                                    <strong>{{ $mSent ? verta($mSent->created_at)->format('Y/m/d ساعت H:i') : 'ثبت نشده' }}</strong>
                                </span>
                            </div>
                            <div class="done">
                                <i class="fa fa-check-circle fa-lg"></i>
                                <span>
                                    <small>زمان پاسخ دادن{{ $lag ? ' (' . $lag . ' بعد از ارسال)' : '' }}</small>
                                    <strong>{{ verta($answeredAt)->format('Y/m/d ساعت H:i') }}</strong>
                                </span>
                            </div>
                        </div>

                        <div class="fb-meta">
                            <div><small>بیمار</small><strong>{{ $mApp?->user?->fullname ?? 'نوبت یافت نشد' }}</strong></div>
                            <div><small>پزشک</small><strong>{{ $mApp?->doctor?->fullname ?? '-' }}</strong></div>
                            <div><small>بخش</small><strong>{{ $mApp?->service?->title ?? '-' }}</strong></div>
                            <div><small>زمان ویزیت</small><strong>{{ $mApp?->date_visit ? verta($mApp->date_visit)->format('Y/m/d') . ' ' . substr((string) $mApp->start_time, 0, 5) : '-' }}</strong></div>
                            <div><small>کد پیگیری</small><strong>{{ $mApp?->tracking_code ?? '-' }}</strong></div>
                        </div>

                        @foreach ($modal['rows'] as $i => $row)
                            @php
                                $q = $row['question'];
                                $a = $row['answer'];
                                $text = $a ? \Modules\AppointmentUser\Livewire\Admin\FeedBack\FeedbackAnswerList::formatAnswer($a) : '';
                            @endphp
                            <div class="fb-qa">
                                <div class="q">{{ $loop->iteration }}. {{ $q?->title ?? 'سوال حذف شده' }}</div>
                                @if ($a && $q?->type === 'rating')
                                    <div class="fb-stars" aria-label="{{ $a->answer }} از ۵">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <span class="{{ $s <= (int) $a->answer ? '' : 'off' }}">★</span>
                                        @endfor
                                        <small class="text-muted ms-2">{{ $a->answer }} از ۵</small>
                                    </div>
                                @elseif ($a && $q?->type === 'checkbox')
                                    <div class="a badges">
                                        @foreach ((array) json_decode((string) $a->answer, true) as $opt)
                                            <span class="badge bg-primary me-1">{{ $opt }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="a {{ $text === '' ? 'none' : '' }}">{{ $text !== '' ? $text : 'بدون پاسخ' }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="close">بستن</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
