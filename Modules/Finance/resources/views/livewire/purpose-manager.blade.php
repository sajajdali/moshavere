<div class="fin-page">
    @include('finance::components.assets')
    <div class="page-header">
        <div>
            <h1 class="page-title">دلایل پرداخت</h1>
        </div>
        <div class="ms-auto pageheader-btn d-flex gap-2">
            <a href="{{ route('admin.finance.payments') }}" class="btn btn-secondary"><i class="fa fa-list me-1"></i> پرداخت ها</a>
        </div>
    </div>
    @include('admin::layouts.components.alert')

    <div class="fin-card">
        <div class="fin-card-head">
            <h3 class="fin-card-title"><i class="fa fa-{{ $editingId ? 'pencil' : 'plus' }}"></i> {{ $editingId ? 'ویرایش دلیل پرداخت' : 'افزودن دلیل پرداخت' }}</h3>
        </div>
        <div class="fin-card-body">
            <p class="text-muted small">دلیل پرداخت مشخص می کند بیمار مبلغ را برای چه چیزی پرداخت کرده است (مثلا بیعانه نوبت بعدی). این فهرست را کلینیک خودش مدیریت می کند و در همهٔ گزارش ها استفاده می شود.</p>
            <form wire:submit.prevent="save" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">عنوان <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" wire:model="title" placeholder="مثال: بیعانه عمل">
                    @error('title') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">ترتیب نمایش</label>
                    <input type="text" inputmode="numeric" class="form-control @error('sort') is-invalid @enderror" wire:model="sort" placeholder="خودکار">
                    @error('sort') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i class="fa fa-check me-1"></i> {{ $editingId ? 'ذخیره' : 'افزودن' }}</button>
                    @if ($editingId)
                        <button type="button" class="btn btn-light" wire:click="cancel">انصراف</button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="fin-card">
        <div class="fin-card-head"><h3 class="fin-card-title"><i class="fa fa-tags"></i> فهرست دلایل پرداخت</h3></div>
        <div class="fin-card-body">
            <div class="table-responsive">
                <table class="table table-hover fin-table text-center mb-0">
                    <thead><tr><th>ترتیب</th><th class="text-start">عنوان</th><th>تعداد پرداخت</th><th>وضعیت</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($purposes as $purpose)
                            <tr wire:key="purpose-{{ $purpose->id }}" class="{{ $purpose->is_active ? '' : 'text-muted' }}">
                                <td>{{ $purpose->sort }}</td>
                                <td class="text-start fw-semibold">{{ $purpose->title }}</td>
                                <td>{{ number_format($purpose->payments_count) }}</td>
                                <td>
                                    <span class="badge {{ $purpose->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $purpose->is_active ? 'فعال' : 'غیرفعال' }}</span>
                                </td>
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="edit({{ $purpose->id }})" title="ویرایش"><i class="fa fa-pencil"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="toggle({{ $purpose->id }})">{{ $purpose->is_active ? 'غیرفعال کردن' : 'فعال کردن' }}</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="delete({{ $purpose->id }})"
                                        wire:confirm="این دلیل پرداخت حذف شود؟" title="حذف"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
