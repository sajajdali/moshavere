<div wire:ignore.self class="modal fade quick-time-modal" id="quickTimeEditModal" data-bs-backdrop="static"
    data-bs-keyboard="false" tabindex="-1" aria-labelledby="quickTimeEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <div class="modal-icon"><i class="fa fa-clock-o" aria-hidden="true"></i></div>
                    <div>
                        <h5 class="modal-title" id="quickTimeEditModalLabel">ویرایش سریع زمان نوبت</h5>
                        <div class="modal-subtitle">تاریخ و ساعت جدید نوبت را وارد کنید.</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>

            <form wire:submit="saveQuickTimeEdit">
                <div class="modal-body">
                    <div class="quick-time-summary">
                        <div>
                            <span>بیمار</span>
                            <strong>{{ $quickTimeEditMeta['patient'] ?? '---' }}</strong>
                        </div>
                        <div>
                            <span>پزشک</span>
                            <strong>{{ $quickTimeEditMeta['doctor'] ?? '---' }}</strong>
                        </div>
                    </div>

                    <div class="quick-time-field">
                        <label for="quick-time-date">تاریخ جدید</label>
                        <input id="quick-time-date" type="text" autocomplete="off" data-jdp
                            data-name="quickTimeEdit.date" wire:model="quickTimeEdit.date"
                            placeholder="برای نمونه ۱۴۰۵/۰۶/۱۸">
                        @error('quickTimeEdit.date')
                            <span class="quick-time-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="quick-time-grid">
                        <div class="quick-time-field">
                            <label for="quick-time-start">ساعت شروع</label>
                            <input id="quick-time-start" type="time" wire:model="quickTimeEdit.start_time">
                            @error('quickTimeEdit.start_time')
                                <span class="quick-time-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="quick-time-field">
                            <label for="quick-time-end">ساعت پایان</label>
                            <input id="quick-time-end" type="time" wire:model="quickTimeEdit.end_time">
                            @error('quickTimeEdit.end_time')
                                <span class="quick-time-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="quick-time-warning">
                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                        <span>در صورت ویرایش از این قسمت، تداخل نوبت‌ها در نظر گرفته نمی‌شود.</span>
                    </div>

                    <label class="quick-time-sms" for="quick-time-send-sms">
                        <input id="quick-time-send-sms" type="checkbox" wire:model="quickTimeEdit.send_sms">
                        <span>
                            <strong>ارسال پیامک تغییر زمان</strong>
                            <small>پیامک با همان سناریو و مشخصات تعریف‌شده در تنظیمات سیستم ارسال می‌شود.</small>
                        </span>
                    </label>
                </div>

                <div class="modal-footer">
                    <button type="button" class="om-btn" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="om-btn om-btn-primary" wire:loading.attr="disabled"
                        wire:target="saveQuickTimeEdit">
                        <span wire:loading.remove wire:target="saveQuickTimeEdit"><i class="fa fa-check"></i> ثبت زمان جدید</span>
                        <span wire:loading wire:target="saveQuickTimeEdit"><i class="fa fa-spinner fa-spin"></i> در حال ثبت...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
