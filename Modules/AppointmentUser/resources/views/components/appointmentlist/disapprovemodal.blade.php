<div class="modal fade" id="resoanForDisapproveModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">عدم تایید نوبت آنلاین</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3"> <label for="validationTextarea" class="form-label">لطفا دلیل رد کردن این نوبت را
                        یادداشت کنید</label>
                    <textarea class="form-control" id="validationTextarea" placeholder="بنویسید..." wire:model='form.reason'></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click='ignoreDisaproveModal'
                    data-bs-dismiss="modal">بیخیال</button>
                <button type="button" wire:click='disaprovedModal' class="btn btn-primary">انجام عملیات</button>
            </div>
        </div>
    </div>
</div>
