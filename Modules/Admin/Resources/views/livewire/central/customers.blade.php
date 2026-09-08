<div>
    <div class="page-header">
        <div><h1 class="page-title">مشتریان</h1><p class="text-muted mb-0">مدیریت کاربران دارای نقش مشتری</p></div>
        <div class="ms-auto pageheader-btn">
            <button type="button" class="btn btn-primary" wire:click="openCustomerModal"><i class="fa fa-user-plus me-1"></i> افزودن مشتری</button>
        </div>
    </div>
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    <div class="card">
        <div class="card-header border-bottom"><h3 class="card-title">فهرست مشتریان</h3></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>نام و نام خانوادگی</th><th>اسم مرکز</th><th>موبایل</th><th>ایمیل</th><th>نقش</th><th>تاریخ ثبت</th></tr></thead>
                    <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td>{{ $customer->full_name }}</td>
                            <td>{{ $customer->center_name }}</td>
                            <td dir="ltr">{{ $customer->mobile }}</td>
                            <td dir="ltr">{{ $customer->email }}</td>
                            <td><span class="badge bg-primary">مشتری</span></td>
                            <td>{{ optional($customer->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">هنوز مشتری ثبت نشده است.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @include('admin::livewire.central.partials.customer-modal')
</div>
