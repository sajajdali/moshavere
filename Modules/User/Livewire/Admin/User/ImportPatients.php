<?php

namespace Modules\User\Livewire\Admin\User;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\User\Entities\User;
use Modules\User\Jobs\ImportPatientsJob;
use Modules\User\Support\PatientImporter;

/**
 * وارد کردن پرونده بیماران از فایل اکسل نرم افزار قبلی (ساخت کاربر جدید یا تکمیل کاربر با همان موبایل).
 * پردازش فایل در یک job پس زمینه انجام می شود و این صفحه فقط پیشرفت آن را نشان می دهد.
 */
class ImportPatients extends Component
{
    use AuthorizesRequests, WithFileUploads;

    public $file;

    public ?string $importId = null;

    /** آخرین وضعیت job: status, processed, total, result, error */
    public ?array $progress = null;

    public function updatedFile(): void
    {
        $this->resetErrorBag();
    }

    public function import(): void
    {
        $this->authorize('create', User::class);

        $this->validate([
            'file' => ['required', 'file', 'max:20480', function ($attribute, $value, $fail) {
                if (! in_array(strtolower($value->getClientOriginalExtension()), ['xls', 'xlsx', 'csv'], true)) {
                    $fail('فرمت فایل باید xls، xlsx یا csv باشد.');
                }
            }],
        ], [
            'file.required' => 'فایل اکسل را انتخاب کنید.',
            'file.max' => 'حجم فایل نباید بیشتر از ۲۰ مگابایت باشد.',
        ]);

        $this->importId = (string) Str::uuid();
        $path = $this->file->storeAs(
            'patient-imports',
            $this->importId.'.'.strtolower($this->file->getClientOriginalExtension()),
            ImportPatientsJob::STORAGE_DISK,
        );
        $this->reset('file');

        ImportPatientsJob::markQueued($this->importId);
        $dispatch = ImportPatientsJob::dispatch($this->importId, $path, auth()->id(), tenant('id'));
        if (config('queue.default') === 'sync') {
            // بدون worker، job بعد از ارسال پاسخ اجرا می شود تا درخواست صفحه منتظر پردازش نماند
            $dispatch->afterResponse();
        }

        $this->refreshProgress();
    }

    public function refreshProgress(): void
    {
        $this->progress = $this->importId ? ImportPatientsJob::status($this->importId) : null;
    }

    public function render()
    {
        return view('user::livewire.admin.user.import-patients', [
            'columns' => PatientImporter::COLUMNS,
            'running' => in_array($this->progress['status'] ?? null, ['queued', 'running'], true),
        ])->title('وارد کردن پرونده بیماران');
    }
}
