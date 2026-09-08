<div>
    <div class="page-header">
        <div><h1 class="page-title">تنظیمات تمدید</h1><p class="text-muted mb-0">مبالغ سالیانه پشتیبانی و سرور را جداگانه تعیین کنید.</p></div>
    </div>
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    <div class="card">
        <div class="card-header border-bottom"><h3 class="card-title">هزینه‌های تمدید سالیانه</h3></div>
        <form wire:submit="save">
            <div class="card-body">
                <div class="row mb-3">
                    <label class="col-md-3 form-label" for="support-cost">هزینه پشتیبانی</label>
                    <div class="col-md-6"><div class="input-group">
                        <input id="support-cost" type="number" min="0" dir="ltr" class="form-control @error('supportRenewCost') is-invalid @enderror" wire:model="supportRenewCost">
                        <span class="input-group-text">تومان</span>
                    </div>@error('supportRenewCost') <span class="text-danger">{{ $message }}</span> @enderror</div>
                </div>
                <div class="row mb-3">
                    <label class="col-md-3 form-label" for="server-cost">هزینه سرور</label>
                    <div class="col-md-6"><div class="input-group">
                        <input id="server-cost" type="number" min="0" dir="ltr" class="form-control @error('serverRenewCost') is-invalid @enderror" wire:model="serverRenewCost">
                        <span class="input-group-text">تومان</span>
                    </div>@error('serverRenewCost') <span class="text-danger">{{ $message }}</span> @enderror</div>
                </div>
                <div class="row"><div class="col-md-6 offset-md-3">
                    <div class="alert alert-info mb-0">جمع فعلی: <strong>{{ number_format((int) $supportRenewCost + (int) $serverRenewCost) }} تومان</strong></div>
                </div></div>
            </div>
            <div class="card-footer text-end"><button class="btn btn-primary" type="submit">ذخیره تنظیمات</button></div>
        </form>
    </div>
</div>
