<?php

namespace Modules\Finance\Livewire\Concerns;

use Hekmatinasser\Verta\Verta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Modules\Finance\app\Models\FinancePaymentPurpose;
use Modules\Finance\Enum\FinancePaymentMethod;
use Modules\User\Entities\User;

/**
 * Jalali date filters, quick ranges and the decoration of the unified report rows
 * shared by the finance pages.
 */
trait InteractsWithFinanceFilters
{
    /** 'Y/m/d' (Jalali, Persian digits allowed) => 'Y-m-d' (Gregorian), null when it is not a valid date */
    protected function toGregorian(?string $jalali): ?string
    {
        $jalali = trim(strtr((string) $jalali, ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '-' => '/']));
        if (! preg_match('#^\d{4}/\d{1,2}/\d{1,2}$#', $jalali)) {
            return null;
        }
        try {
            return Verta::parse($jalali)->toCarbon()->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** the filters of the page, ready for FinanceReportService (Gregorian dates, empty values removed) */
    protected function serviceFilters(array $extra = []): array
    {
        $filters = [
            'from' => $this->toGregorian($this->filter['from'] ?? null),
            'to' => $this->toGregorian($this->filter['to'] ?? null),
            'doctor_id' => $this->filter['doctor_id'] ?? null,
            'method' => $this->filter['method'] ?? null,
            'purpose_id' => $this->filter['purpose_id'] ?? null,
            'source' => $this->filter['source'] ?? null,
            'type' => $this->filter['type'] ?? null,
            'search' => $this->filter['search'] ?? null,
        ];

        return array_filter($extra + $filters, fn ($value) => filled($value));
    }

    /** a quick period for the report: today, week, month, last_month, year, all */
    public function setRange(string $range): void
    {
        $today = Verta::now();
        [$from, $to] = match ($range) {
            'today' => [$today->copy(), $today->copy()],
            'week' => [$today->copy()->subDays(6), $today->copy()],
            'month' => [$today->copy()->startMonth(), $today->copy()],
            'last_month' => [$today->copy()->subMonth()->startMonth(), $today->copy()->subMonth()->endMonth()],
            'year' => [$today->copy()->startYear(), $today->copy()],
            default => [null, null],
        };
        $this->filter['from'] = $from?->format('Y/m/d');
        $this->filter['to'] = $to?->format('Y/m/d');
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->filter = $this->defaultFilter();
        $this->resetPage();
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    /** the select options used by the filter bars */
    protected function filterOptions(): array
    {
        return [
            'doctors' => User::doctors_query()?->get() ?? collect(),
            'purposes' => FinancePaymentPurpose::ordered()->get(),
            'methods' => FinancePaymentMethod::reportLabels(),
        ];
    }

    /** adds the patient / doctor / purpose / method names to the unified rows */
    protected function decorateRows(Collection $rows): Collection
    {
        $users = User::whereIn('id', $rows->pluck('user_id')->merge($rows->pluck('doctor_id'))->filter()->unique())->get()->keyBy('id');
        $purposes = FinancePaymentPurpose::pluck('title', 'id');
        $methods = FinancePaymentMethod::reportLabels();

        return $rows->map(function ($row) use ($users, $purposes, $methods) {
            $patient = $users[$row->user_id] ?? null;
            $row->patient_name = $patient ? ($patient->fullName ?: $patient->mobile) : 'کاربر حذف شده';
            $row->patient_mobile = $patient?->mobile;
            $row->doctor_name = $row->doctor_id ? ($users[$row->doctor_id]?->fullName ?: '-') : null;
            $row->method_label = $methods[$row->method_key] ?? $row->method_key;
            $row->purpose_label = $row->purpose_id ? ($purposes[$row->purpose_id] ?? '-') : ($row->source === 'system' ? 'پرداخت سیستمی نوبت' : null);
            $row->is_manual = $row->source === 'manual';
            $row->is_refund = $row->kind === 'refund';
            $row->paid_at_carbon = \Illuminate\Support\Carbon::parse($row->paid_at);

            return $row;
        });
    }

    protected function can(string $permission): bool
    {
        return (bool) Auth::user()?->can($permission);
    }

    protected function authorizeFinance(string $permission): void
    {
        abort_unless($this->can($permission), 403);
    }
}
