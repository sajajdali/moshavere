<div @if ($running) wire:poll.2s="refreshProgress" @endif>
    <style>
        .ip-table-wrap{overflow-x:auto;border:1px solid #e3e7ee;border-radius:14px;background:#fff}
        .ip-table{width:100%;min-width:640px;border-collapse:separate;border-spacing:0;text-align:center;white-space:nowrap}
        .ip-table th,.ip-table td{padding:12px 16px;border-bottom:1px solid #eef0f4}
        .ip-table tbody tr:last-child td{border-bottom:0}
        .ip-table th{background:#f6f8fb;font-weight:600;vertical-align:top}
        .ip-table th.is-required{background:#e6f4f2}
        .ip-col-key{display:block;color:#101828;font-size:14px;font-weight:800;letter-spacing:.2px}
        .ip-col-label{display:block;margin-top:2px;color:#667085;font-size:12px;font-weight:500}
        .ip-table td{color:#344054;font-size:14px}
        .ip-table tbody tr:hover td{background:#fafbfc}
        .ip-table td.ip-empty{color:#c0c6d0}
        .ip-stat{height:100%;padding:16px 12px;border-radius:14px;background:#f6f8fb;text-align:center}
        .ip-stat-value{font-size:24px;font-weight:800;line-height:1.3}
        .ip-stat-label{margin-top:2px;color:#667085;font-size:13px}
        .ip-stat.is-success{background:#ecfdf3}.ip-stat.is-primary{background:#eff8ff}.ip-stat.is-danger{background:#fef3f2}
        .ip-issues{border:1px solid #e3e7ee;border-radius:14px;overflow:hidden}
        .ip-issues table{margin:0}
        .ip-issues th{background:#f6f8fb;border-bottom:1px solid #e3e7ee}
        .ip-issues td{border-color:#eef0f4}
    </style>
    <div class="page-header mb-5">
        <div>
            <h1 class="page-title">وارد کردن پرونده بیماران</h1>
        </div>
    </div>

    @include('admin::layouts.components.alert')

    <div class="card">
        <div class="card-header border-bottom">
            <h3 class="card-title">ساختار فایل اکسل</h3>
        </div>
        <div class="card-body">
            <p class="text-muted">
                سطر اول فایل باید عنوان ستون‌ها باشد. ستون‌های زیر خوانده می‌شوند و بقیه ستون‌های فایل (مثل آدرس، تلفن، شغل و ...) نادیده گرفته می‌شوند.
                ترتیب ستون‌ها مهم نیست.
            </p>

            <div class="ip-table-wrap mb-4">
                <table class="ip-table" dir="ltr">
                    <thead>
                        <tr>
                            @foreach ($columns as $title => $label)
                                <th class="{{ $title === 'Mobile' ? 'is-required' : '' }}">
                                    <span class="ip-col-key">{{ $title }}</span>
                                    <span class="ip-col-label" dir="rtl">{{ $label }}@if ($title === 'Mobile') <b class="text-danger">*</b>@endif</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1001</td><td>نرگس</td><td>فلاحی</td><td>0051093278</td><td>09123006963</td><td>21</td>
                        </tr>
                        <tr>
                            <td>1002</td><td>میلاد</td><td>بخشی</td><td>3861207699</td><td>09123680324</td><td>20</td>
                        </tr>
                        <tr>
                            <td>1003</td><td>فاطمه</td><td>کاظمی</td><td class="ip-empty">—</td><td>09398536085</td><td class="ip-empty">—</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <ul class="mb-0 text-muted">
                <li><b>Mobile</b> الزامی است (<span class="text-danger">*</span>)؛ ردیف بدون موبایل معتبر (۱۱ رقم و شروع با 09) وارد نمی‌شود.</li>
                <li><b>Gender</b>: عدد ۲۱ یعنی زن و ۲۰ یعنی مرد. اگر ستون <b>GenderDesc</b> با مقدار «زن» یا «مرد» در فایل باشد، از آن هم استفاده می‌شود.</li>
                <li><b>ParvandeCode</b> به‌عنوان «شماره پرونده» و <b>MelliCode</b> به‌عنوان «کد ملی» ذخیره می‌شود.</li>
                <li>اگر کاربری با همان شماره موبایل وجود داشته باشد، فقط اطلاعات خالی او تکمیل می‌شود و اطلاعات قبلی تغییر نمی‌کند؛ در غیر این صورت کاربر جدید با نقش پیش‌فرض ساخته می‌شود.</li>
                <li>فرمت‌های مجاز فایل: xls، xlsx و csv.</li>
            </ul>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom">
            <h3 class="card-title">انتخاب فایل</h3>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="import">
                <div class="row align-items-end g-3">
                    <div class="col-md-8">
                        <label class="form-label" for="patients-file">فایل اکسل بیماران</label>
                        <input type="file" id="patients-file" wire:model="file" accept=".xls,.xlsx,.csv"
                            class="form-control @error('file') is-invalid @enderror">
                        <div wire:loading wire:target="file" class="text-muted mt-1">در حال بارگذاری فایل...</div>
                        @error('file')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100" wire:loading.attr="disabled" wire:target="file,import" @disabled($running)>
                            <span wire:loading.remove wire:target="import"><i class="fa fa-upload me-1"></i> وارد کردن اطلاعات</span>
                            <span wire:loading wire:target="import"><i class="fa fa-spinner fa-spin me-1"></i> در حال پردازش، لطفاً صبر کنید...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($running)
        <div class="card">
            <div class="card-body">
                @php($percent = ($progress['total'] ?? 0) > 0 ? (int) floor($progress['processed'] / $progress['total'] * 100) : 0)
                <div class="d-flex justify-content-between mb-2">
                    <span><i class="fa fa-spinner fa-spin me-1"></i>
                        {{ ($progress['status'] ?? '') === 'queued' ? 'فایل در صف پردازش است...' : 'در حال وارد کردن اطلاعات در پس‌زمینه...' }}
                    </span>
                    <span class="text-muted">{{ number_format($progress['processed'] ?? 0) }} از {{ number_format($progress['total'] ?? 0) }}</span>
                </div>
                <div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width: {{ $percent }}%"></div></div>
                <p class="text-muted mt-2 mb-0">می‌توانید این صفحه را باز نگه دارید؛ نتیجه پس از پایان پردازش نمایش داده می‌شود.</p>
            </div>
        </div>
    @elseif (($progress['status'] ?? null) === 'failed')
        <div class="alert alert-danger">
            <i class="fa fa-remove me-2"></i> وارد کردن اطلاعات ناموفق بود: {{ $progress['error'] }}
        </div>
    @elseif ($result = $progress['result'] ?? null)
        <div class="card">
            <div class="card-header border-bottom">
                <h3 class="card-title">نتیجه وارد کردن اطلاعات</h3>
            </div>
            <div class="card-body">
                <div class="row text-center g-3 mb-4">
                    <div class="col-6 col-md"><div class="ip-stat"><div class="ip-stat-value">{{ number_format($result['total']) }}</div><div class="ip-stat-label">کل ردیف‌ها</div></div></div>
                    <div class="col-6 col-md"><div class="ip-stat is-success"><div class="ip-stat-value text-success">{{ number_format($result['created']) }}</div><div class="ip-stat-label">کاربر جدید</div></div></div>
                    <div class="col-6 col-md"><div class="ip-stat is-primary"><div class="ip-stat-value text-primary">{{ number_format($result['updated']) }}</div><div class="ip-stat-label">تکمیل‌شده</div></div></div>
                    <div class="col-6 col-md"><div class="ip-stat"><div class="ip-stat-value text-secondary">{{ number_format($result['unchanged']) }}</div><div class="ip-stat-label">بدون تغییر</div></div></div>
                    <div class="col-6 col-md"><div class="ip-stat is-danger"><div class="ip-stat-value text-danger">{{ number_format($result['skipped']) }}</div><div class="ip-stat-label">ردشده</div></div></div>
                </div>

                @if ($result['issues'])
                    <h5 class="mb-3">مشکلات پیش‌آمده</h5>
                    <div class="ip-issues" style="max-height: 360px; overflow-y: auto;">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr><th style="width: 90px">ردیف</th><th style="width: 90px">نوع</th><th>توضیح</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($result['issues'] as $issue)
                                    <tr>
                                        <td>{{ $issue['row'] }}</td>
                                        <td><span class="badge bg-{{ $issue['level'] === 'error' ? 'danger' : 'warning' }}">{{ $issue['level'] === 'error' ? 'ردشد' : 'هشدار' }}</span></td>
                                        <td>{{ $issue['message'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($result['issues_truncated'])
                        <p class="text-muted mt-2 mb-0">فقط {{ \Modules\User\Support\PatientImporter::MAX_REPORTED_ISSUES }} مورد اول نمایش داده شده است.</p>
                    @endif
                @else
                    <p class="text-success mb-0"><i class="fa fa-check-circle me-1"></i> همه ردیف‌ها بدون مشکل پردازش شدند.</p>
                @endif
            </div>
        </div>
    @endif
</div>
