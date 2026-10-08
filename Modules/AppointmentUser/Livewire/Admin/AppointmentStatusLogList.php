<?php

namespace Modules\AppointmentUser\Livewire\Admin;

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppointmentUser\app\Models\AppointmentStatusLog;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;

#[Title('تاریخچه تغییر وضعیت نوبت‌ها')]
class AppointmentStatusLogList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $event = '';
    public string $toStatus = '';
    public string $patient = '';
    public string $doctorId = '';
    public string $changedBy = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount(): void
    {
        // فقط مدیر کل
        abort_unless(auth()->user()?->can('SUPER_ADMIN'), 403);
    }

    public function updating($name): void
    {
        if (in_array($name, ['search', 'event', 'toStatus', 'patient', 'doctorId', 'changedBy', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'event', 'toStatus', 'patient', 'doctorId', 'changedBy', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function render()
    {
        abort_unless(auth()->user()?->can('SUPER_ADMIN'), 403);

        $search = trim($this->search);

        $logs = AppointmentStatusLog::query()
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('tracking_code', 'like', "%{$search}%")
                    ->orWhere('patient_name', 'like', "%{$search}%")
                    ->orWhere('patient_mobile', 'like', "%{$search}%")
                    ->orWhere('doctor_name', 'like', "%{$search}%")
                    ->orWhere('changed_by_name', 'like', "%{$search}%")
                    ->orWhere('appointment_user_id', $search);
            }))
            ->when($this->event !== '', fn ($q) => $q->where('event', $this->event))
            ->when($this->toStatus !== '', fn ($q) => $q->where('to_status', (int) $this->toStatus))
            ->when(trim($this->patient) !== '', fn ($q) => $q->where(function ($q) {
                $term = trim($this->patient);
                $q->where('patient_name', 'like', "%{$term}%")->orWhere('patient_mobile', 'like', "%{$term}%");
            }))
            ->when($this->doctorId !== '', fn ($q) => $q->where('doctor_id', (int) $this->doctorId))
            ->when($this->changedBy !== '', fn ($q) => $q->where('changed_by_name', $this->changedBy))
            ->when($from = $this->parseDate($this->dateFrom), fn ($q) => $q->whereDate('date_visit', '>=', $from))
            ->when($to = $this->parseDate($this->dateTo), fn ($q) => $q->whereDate('date_visit', '<=', $to))
            ->latest('id')
            ->paginate(25);

        return view('appointmentuser::livewire.admin.appointment-status-log-list', [
            'logs' => $logs,
            'events' => AppointmentStatusLog::eventLabels(),
            'statuses' => AppointmentUserStatusEnum::cases(),
            'doctors' => AppointmentStatusLog::query()->whereNotNull('doctor_id')
                ->select('doctor_id', 'doctor_name')->distinct()->orderBy('doctor_name')->get(),
            'changers' => AppointmentStatusLog::query()->whereNotNull('changed_by_name')
                ->distinct()->orderBy('changed_by_name')->pluck('changed_by_name'),
        ]);
    }

    /** تاریخ شمسی (۱۴۰۵/۰۷/۱۶) به تاریخ میلادی؛ ورودی نامعتبر نادیده گرفته می‌شود */
    private function parseDate(string $value): ?string
    {
        $value = trim(strtr($value, ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9']));
        if ($value === '') {
            return null;
        }
        try {
            return \Verta::parse($value)->toCarbon()->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
