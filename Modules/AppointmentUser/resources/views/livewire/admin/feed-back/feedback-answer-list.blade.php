<div>
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
                <table class="table text-nowrap table-bordered text-center">
                    <thead>
                        <tr class="table-primary">
                            <th>#</th>
                            <th>فرم</th>
                            <th>نام کاربر</th>
                            <th>نام پزشک</th>
                            <th>بخش</th>
                            <th>زمان ویزیت</th>
                            <th>زمان انجام نظرسنجی</th>
                            <th>پاسخ ها</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($submissions as $row)
                            @php
                                $key = $row->appointment_user_id . '-' . $row->feedback_form_id;
                                $app = $appointments->get($row->appointment_user_id);
                            @endphp
                            <tr wire:key="row-{{ $key }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $forms->get($row->feedback_form_id)?->title ?? '-' }}</td>
                                <td>{{ $app?->user?->fullname ?? 'نوبت یافت نشد' }}</td>
                                <td>{{ $app?->doctor?->fullname ?? '-' }}</td>
                                <td>{{ $app?->service?->title ?? '-' }}</td>
                                <td>{{ $app?->date_visit ? verta($app->date_visit)->format('Y/m/d ساعت H:i') : '-' }}</td>
                                <td>{{ verta($row->answered_at)->format('Y/m/d ساعت H:i') }}</td>
                                <td>
                                    <a href="#" wire:click.prevent="toggle('{{ $key }}')">
                                        {{ $opened === $key ? 'بستن' : 'مشاهده' }}
                                    </a>
                                </td>
                            </tr>
                            @if ($opened === $key)
                                <tr wire:key="detail-{{ $key }}">
                                    <td colspan="100%" class="text-start bg-light">
                                        @foreach ($details as $answer)
                                            <div class="mb-2">
                                                <strong>{{ $answer->question?->title ?? 'سوال حذف شده' }}</strong>
                                                <div class="text-muted" style="white-space: normal">
                                                    {{ \Modules\AppointmentUser\Livewire\Admin\FeedBack\FeedbackAnswerList::formatAnswer($answer) ?: '-' }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="100%">
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
</div>
