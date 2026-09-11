@extends('onlineconsultation::shell')
@section('consultation-title','یادآوری‌های پیامکی نوبت تلفنی')
@section('consultation-description','تعریف یادآوری‌های بیمار و مشاور و مشاهده سوابق هر نوبت تلفنی')
@section('consultation-content')
@php
    $isEdit = (bool) $editingRule;
    $storedMinutes = $editingRule?->minutes_before ?? 15;
    $defaultUnit = $storedMinutes % 60 === 0 ? 'hour' : 'minute';
    $defaultOffset = $defaultUnit === 'hour' ? $storedMinutes / 60 : $storedMinutes;
    $statusLabels = ['pending'=>'در انتظار','queued'=>'در صف','retrying'=>'تلاش مجدد','sent'=>'ارسال‌شده','failed'=>'ناموفق','skipped'=>'ارسال‌نشده'];
@endphp
<div class="oc-stack">
    <section class="oc-panel">
        <div class="oc-panel-header">
            <h2 class="oc-panel-title"><i class="fa-solid fa-bell" aria-hidden="true"></i>{{ $isEdit ? 'ویرایش قانون یادآوری' : 'افزودن یادآوری جدید' }}</h2>
            @if($isEdit)<a class="oc-btn oc-btn-small" href="{{ route('admin.consultation.sms-reminders.index') }}">انصراف از ویرایش</a>@endif
        </div>
        <form method="POST" action="{{ $isEdit ? route('admin.consultation.sms-reminders.update', $editingRule) : route('admin.consultation.sms-reminders.store') }}">
            @csrf
            @if($isEdit) @method('PUT') @endif
            <div class="oc-panel-body oc-grid">
                <div class="oc-field">
                    <label class="oc-label" for="title">عنوان یادآوری <span class="oc-required">*</span></label>
                    <input class="oc-input" id="title" name="title" maxlength="255" required value="{{ old('title', $editingRule?->title) }}" placeholder="مثلاً یادآوری ۱۵ دقیقه قبل برای بیمار">
                    @error('title')<small class="oc-error">{{ $message }}</small>@enderror
                </div>
                <div class="oc-field">
                    <label class="oc-label" for="recipient_type">گیرنده <span class="oc-required">*</span></label>
                    <select class="oc-input" id="recipient_type" name="recipient_type" required>
                        <option value="patient" @selected(old('recipient_type', $editingRule?->recipient_type ?? 'patient') === 'patient')>بیمار</option>
                        <option value="practitioner" @selected(old('recipient_type', $editingRule?->recipient_type) === 'practitioner')>پزشک / مشاور نوبت</option>
                    </select>
                    <small class="oc-help">شماره بیمار یا شماره پزشک/مشاور متصل به همان نوبت استفاده می‌شود.</small>
                    @error('recipient_type')<small class="oc-error">{{ $message }}</small>@enderror
                </div>
                <div class="oc-field">
                    <label class="oc-label" for="offset_value">زمان ارسال قبل از نوبت <span class="oc-required">*</span></label>
                    <div class="oc-inline">
                        <input class="oc-input" style="flex:1" id="offset_value" name="offset_value" type="number" min="1" max="10080" required value="{{ old('offset_value', $defaultOffset) }}">
                        <select class="oc-input" style="width:130px" name="offset_unit" aria-label="واحد زمان">
                            <option value="minute" @selected(old('offset_unit', $defaultUnit) === 'minute')>دقیقه</option>
                            <option value="hour" @selected(old('offset_unit', $defaultUnit) === 'hour')>ساعت</option>
                        </select>
                    </div>
                    <small class="oc-help">نمونه: ۱۵ دقیقه، ۲۰ دقیقه یا ۳ ساعت قبل از شروع مشاوره.</small>
                    @error('offset_value')<small class="oc-error">{{ $message }}</small>@enderror
                    @error('offset_unit')<small class="oc-error">{{ $message }}</small>@enderror
                </div>
                <div class="oc-field">
                    <label class="oc-label" for="template">نام قالب پیامکی <span class="oc-required">*</span></label>
                    <input class="oc-input oc-ltr" id="template" name="template" maxlength="255" required value="{{ old('template', $editingRule?->template) }}" placeholder="نام یا کد الگوی ثبت‌شده در پنل پیامک">
                    @error('template')<small class="oc-error">{{ $message }}</small>@enderror
                </div>
                <div class="oc-field oc-full">
                    <label class="oc-label" for="message_text">متن پیامک برای پنل‌های ارسال متنی</label>
                    <textarea class="oc-input" id="message_text" name="message_text" maxlength="2000" placeholder="برای پارس، فراز و استارپیام متن را با %param1% تا %param9% وارد کنید.">{{ old('message_text', $editingRule?->message_text) }}</textarea>
                    <small class="oc-help">برای پنل‌های الگویی مثل SHSMS و IPPanel لازم نیست؛ نام قالب بالا استفاده می‌شود.</small>
                    @error('message_text')<small class="oc-error">{{ $message }}</small>@enderror
                </div>
                <div class="oc-full">
                    <input type="hidden" name="active" value="0">
                    <label class="oc-check">
                        <input class="oc-check-input" type="checkbox" name="active" value="1" @checked((bool) old('active', $editingRule?->active ?? true))>
                        <span><strong class="oc-check-title">یادآوری فعال باشد</strong><small class="oc-help">فقط برای نوبت‌های تلفنی تأییدشده و آینده برنامه‌ریزی می‌شود.</small></span>
                    </label>
                </div>
                <div class="oc-full oc-notice" style="margin:0">
                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                    <div>
                        <strong>راهنمای پارامترهای قالب</strong>
                        <p>پارامترها برای قالب بیمار و پزشک/مشاور یکسان و به ترتیب زیر هستند:</p>
                        <div class="oc-grid" style="margin-top:8px;gap:2px 28px">
                            <span>۱ = نام کاربر</span><span>۲ = نام خانوادگی کاربر</span>
                            <span>۳ = نام پزشک / مشاور</span><span>۴ = نام بخش</span>
                            <span>۵ = تاریخ نوبت</span><span>۶ = ساعت نوبت</span>
                            <span>۷ = لینک جزئیات نوبت</span><span>۸ = شماره پیگیری</span>
                            <span>۹ = آیدی نوبت</span>
                        </div>
                        <small class="oc-help">در متن‌های مستقیم از %param1% تا %param9% و در پنل‌های الگویی از param1 تا param9 استفاده کنید.</small>
                    </div>
                </div>
            </div>
            <div class="oc-savebar">
                <p>نوع نوبت این بخش همیشه «تلفنی (VOIP)» است.</p>
                <button class="oc-btn oc-btn-primary" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>{{ $isEdit ? 'ذخیره تغییرات' : 'افزودن یادآوری' }}</button>
            </div>
        </form>
    </section>

    <section class="oc-panel">
        <div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-list-check" aria-hidden="true"></i>قوانین یادآوری<span class="oc-badge">{{ $rules->count() }} مورد</span></h2></div>
        <div class="oc-table-wrap">
            <table class="oc-table"><thead><tr><th>عنوان</th><th>گیرنده</th><th>زمان</th><th>قالب</th><th>وضعیت</th><th>ارسال موفق</th><th>مدیریت</th></tr></thead><tbody>
            @forelse($rules as $rule)
                <tr>
                    <td class="oc-person-name">{{ $rule->title }}</td>
                    <td>{{ $rule->recipientLabel() }}</td>
                    <td>{{ $rule->offsetLabel() }}</td>
                    <td class="oc-ltr">{{ $rule->template ?: 'تعریف نشده' }}</td>
                    <td><span class="oc-badge {{ $rule->active ? 'oc-badge-success' : '' }}">{{ $rule->active ? 'فعال' : 'غیرفعال' }}</span></td>
                    <td><span class="oc-count-badge">{{ $rule->sent_count }}</span></td>
                    <td><div class="oc-inline">
                        <a class="oc-btn oc-btn-small" href="{{ route('admin.consultation.sms-reminders.index', ['edit'=>$rule->id]) }}"><i class="fa-solid fa-pen"></i>ویرایش</a>
                        <form method="POST" action="{{ route('admin.consultation.sms-reminders.destroy', $rule) }}" onsubmit="return confirm('این یادآوری حذف شود؟ سوابق ارسال‌شده حفظ می‌شوند.')">@csrf @method('DELETE')<button class="oc-btn oc-btn-small oc-btn-danger" type="submit">حذف</button></form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="7" class="oc-empty-cell"><div class="oc-empty"><i class="fa-solid fa-bell-slash"></i><h3>یادآوری‌ای تعریف نشده است</h3></div></td></tr>
            @endforelse
            </tbody></table>
        </div>
    </section>

    <section class="oc-panel" id="deliveries">
        <div class="oc-panel-header">
            <h2 class="oc-panel-title"><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>ارسال‌های مرتبط با نوبت‌ها</h2>
            <form class="oc-inline" method="GET" action="{{ route('admin.consultation.sms-reminders.index') }}">
                <input class="oc-input" style="width:180px" name="appointment" value="{{ $filters['appointment'] ?? '' }}" placeholder="آیدی یا شماره پیگیری">
                <select class="oc-input" style="width:145px" name="recipient_type"><option value="">همه گیرنده‌ها</option><option value="patient" @selected(($filters['recipient_type'] ?? '') === 'patient')>بیمار</option><option value="practitioner" @selected(($filters['recipient_type'] ?? '') === 'practitioner')>مشاور</option></select>
                <select class="oc-input" style="width:145px" name="status"><option value="">همه وضعیت‌ها</option>@foreach($statusLabels as $value=>$label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select>
                <button class="oc-btn" type="submit">فیلتر</button>
                @if(array_filter([$filters['appointment'] ?? null, $filters['recipient_type'] ?? null, $filters['status'] ?? null]))<a class="oc-btn" href="{{ route('admin.consultation.sms-reminders.index') }}#deliveries">پاک‌کردن</a>@endif
            </form>
        </div>
        <div class="oc-table-wrap">
            <table class="oc-table"><thead><tr><th>نوبت</th><th>بیمار / مشاور</th><th>یادآوری</th><th>گیرنده</th><th>زمان برنامه‌ریزی</th><th>زمان ارسال</th><th>وضعیت</th><th>نتیجه</th></tr></thead><tbody>
            @forelse($deliveries as $item)
                <tr>
                    <td><strong>#{{ $item->appointment?->tracking_code ?: $item->appointment_id }}</strong><small class="oc-cell-sub">آیدی: {{ $item->appointment_id }}</small></td>
                    <td>{{ $item->appointment?->user?->fullName ?: '—' }}<small class="oc-cell-sub">مشاور: {{ $item->appointment?->doctor?->fullName ?: '—' }}</small></td>
                    <td>{{ $item->rule_title ?: $item->reminderRule?->title ?: $item->type }}<small class="oc-cell-sub oc-ltr">قالب: {{ $item->template }}</small></td>
                    <td><span class="oc-badge">{{ $item->recipient_type === 'practitioner' ? 'مشاور' : ($item->recipient_type === 'patient' ? 'بیمار' : 'نامشخص') }}</span><small class="oc-cell-sub oc-ltr">{{ $item->recipient }}</small></td>
                    <td>{{ verta($item->scheduled_at)->format('Y/m/d H:i') }}</td>
                    <td>{{ $item->sent_at ? verta($item->sent_at)->format('Y/m/d H:i:s') : '—' }}</td>
                    <td><span class="oc-badge {{ $item->status === 'sent' ? 'oc-badge-success' : ($item->status === 'failed' ? 'oc-badge-danger' : '') }}">{{ $statusLabels[$item->status] ?? $item->status }}</span><small class="oc-cell-sub">تلاش: {{ $item->attempts }}</small></td>
                    <td>{{ $item->error_message ?: $item->provider_response ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="oc-empty-cell"><div class="oc-empty"><i class="fa-solid fa-message"></i><h3>هنوز ارسالی برای نوبت‌ها ثبت نشده است</h3><p>پس از ثبت نوبت تلفنی تأییدشده، برنامه ارسال آن در این جدول دیده می‌شود.</p></div></td></tr>
            @endforelse
            </tbody></table>
        </div>
        {{ $deliveries->links('onlineconsultation::components.pagination') }}
    </section>
</div>
@endsection
