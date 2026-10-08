<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">فرم های نظرسنجی</h1>
        </div>
        <div class="ms-auto pageheader-btn d-flex gap-2">
            <a href="{{ route('admin.appointment.feedback.create') }}" class="btn btn-primary"><i class="fa fa-plus me-1"></i> ایجاد فرم نظرسنجی</a>
            <a href="{{ route('admin.appointment.feedback.answers') }}" class="btn btn-secondary">نظرسنجی های انجام شده</a>
        </div>
    </div>
    @include('admin::layouts.components.alert')

    <div class="card custom-card" wire:loading.class="op-0-3">
        <div class="card-header border-bottom">
            <h3 class="card-title">لیست فرم های نظرسنجی</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive mb-3">
                <table class="table text-nowrap table-bordered text-center">
                    <thead>
                        <tr class="table-primary">
                            <th>#</th>
                            <th>عنوان</th>
                            <th>پزشک</th>
                            <th>بخش</th>
                            <th>مطب</th>
                            <th>تعداد سوالات</th>
                            <th>پاسخ های ثبت شده</th>
                            <th>وضعیت</th>
                            <th>زمان ایجاد</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($forms as $form)
                            <tr wire:key="form-{{ $form->id }}">
                                <td>{{ $form->id }}</td>
                                <td>{{ $form->title }}</td>
                                <td>{{ $form->doctor?->fullname ?? 'همه' }}</td>
                                <td>{{ $form->service?->title ?? 'همه' }}</td>
                                <td>{{ $form->place?->title ?? 'همه' }}</td>
                                <td>{{ $form->questions_count }}</td>
                                <td>
                                    <a href="{{ route('admin.appointment.feedback.answers', ['formId' => $form->id]) }}">{{ $form->answers_count }}</a>
                                </td>
                                <td>
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox" @checked($form->active)
                                            wire:click="toggleActive({{ $form->id }})">
                                    </div>
                                </td>
                                <td>{{ verta($form->created_at)->format('Y/m/d H:i') }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger"
                                        wire:click="deleteForm({{ $form->id }})"
                                        wire:confirm="این فرم نظرسنجی حذف شود؟">حذف</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%">
                                    <div class="alert alert-info mb-0">هیچ فرم نظرسنجی ثبت نشده است</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">{{ $forms->links() }}</div>
        </div>
    </div>
</div>
