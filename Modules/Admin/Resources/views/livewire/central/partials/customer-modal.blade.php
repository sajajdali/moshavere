<div>
@if ($showCustomerModal)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">افزودن مشتری جدید</h5>
                    <button type="button" class="btn-close" wire:click="closeCustomerModal" aria-label="بستن"></button>
                </div>
                <form wire:submit="createCustomer">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="customer-first-name">نام</label>
                                <input id="customer-first-name" class="form-control @error('customerForm.first_name') is-invalid @enderror" wire:model="customerForm.first_name">
                                @error('customerForm.first_name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="customer-last-name">نام خانوادگی</label>
                                <input id="customer-last-name" class="form-control @error('customerForm.last_name') is-invalid @enderror" wire:model="customerForm.last_name">
                                @error('customerForm.last_name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label" for="customer-center">اسم مرکز</label>
                                <input id="customer-center" class="form-control @error('customerForm.center_name') is-invalid @enderror" wire:model="customerForm.center_name">
                                @error('customerForm.center_name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="customer-mobile">موبایل</label>
                                <input id="customer-mobile" dir="ltr" class="form-control @error('customerForm.mobile') is-invalid @enderror" wire:model="customerForm.mobile">
                                @error('customerForm.mobile') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="customer-email">ایمیل <span class="text-muted">(اختیاری)</span></label>
                                <input id="customer-email" dir="ltr" type="email" class="form-control @error('customerForm.email') is-invalid @enderror" wire:model="customerForm.email">
                                @error('customerForm.email') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="customer-password">کلمه عبور</label>
                                <input id="customer-password" dir="ltr" type="password" class="form-control @error('customerForm.password') is-invalid @enderror" wire:model="customerForm.password">
                                @error('customerForm.password') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="customer-password-confirmation">تکرار کلمه عبور</label>
                                <input id="customer-password-confirmation" dir="ltr" type="password" class="form-control" wire:model="customerForm.password_confirmation">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeCustomerModal">انصراف</button>
                        <button type="submit" class="btn btn-success" wire:loading.attr="disabled" wire:target="createCustomer">
                            <span wire:loading.remove wire:target="createCustomer">ثبت مشتری</span>
                            <span wire:loading wire:target="createCustomer">در حال ثبت...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
@endif
</div>
