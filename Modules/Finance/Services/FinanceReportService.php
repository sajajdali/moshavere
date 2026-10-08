<?php

namespace Modules\Finance\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\Finance\app\Models\FinancePaymentPurpose;
use Modules\Finance\Enum\FinancePaymentMethod;
use Modules\User\Entities\User;
use Modules\User\Enum\UserMetaEnum;

/**
 * Every money movement of a patient in one list:
 *  - `system`: the successful payments of the transactions table (online, wallet, ...)
 *  - `manual`: the payments / refunds registered by the clinic (POS, card to card, cash)
 *
 * Rows share the same columns: source, row_id, user_id, paid_at, amount (signed: a refund is negative),
 * kind (payment|refund), method_key, purpose_id, appointment_id, doctor_id, reference, description.
 *
 * Filters: from, to (Y-m-d), user_id, doctor_id, method, purpose_id, source (system|manual), type (payment|refund), search
 */
class FinanceReportService
{
    private function manualQuery(): Builder
    {
        return DB::table('finance_payments')
            ->whereNull('deleted_at')
            ->selectRaw("'manual' as source, id as row_id, user_id, paid_at,
                IF(type = 'refund', -amount, amount) as amount, type as kind, method as method_key, purpose_id,
                appointment_user_id as appointment_id, doctor_id, reference_number as reference, description");
    }

    private function systemQuery(): Builder
    {
        $p = DB::getTablePrefix();

        return DB::table('transactions')
            ->leftJoin('appointment_users', function ($join) {
                $join->on('appointment_users.id', '=', 'transactions.transactionable_id')
                    ->where('transactions.transactionable_type', '=', AppointmentUser::class);
            })
            ->where('transactions.status', 1)
            ->where('transactions.total_cost', '>', 0)
            ->selectRaw("'system' as source, {$p}transactions.id as row_id, {$p}transactions.user_id, {$p}transactions.created_at as paid_at,
                {$p}transactions.total_cost as amount, 'payment' as kind,
                CASE {$p}transactions.paid_by WHEN 1 THEN 'sys_online' WHEN 2 THEN 'sys_card_to_card' WHEN 3 THEN 'sys_by_admin' WHEN 5 THEN 'sys_wallet' ELSE 'sys_other' END as method_key,
                NULL as purpose_id, {$p}appointment_users.id as appointment_id, {$p}appointment_users.doctor_id as doctor_id,
                {$p}transactions.transaction_code as reference, NULL as description");
    }

    /** patients whose name or mobile contains the term */
    public function patientIdsMatching(string $term): Collection
    {
        $term = trim($term);

        return User::query()
            ->where('mobile', 'like', "%{$term}%")
            ->orWhereHas('metas', fn ($q) => $q
                ->whereIn('meta_key', [UserMetaEnum::FIRST_NAME->value, UserMetaEnum::LAST_NAME->value])
                ->where('meta_value', 'like', "%{$term}%"))
            ->limit(500)
            ->pluck('id');
    }

    /** the unified rows with the filters applied; select / group / order is up to the caller */
    public function query(array $filters = []): Builder
    {
        $source = $filters['source'] ?? null;
        $union = null;
        if ($source !== 'system') {
            $union = $this->manualQuery();
        }
        if ($source !== 'manual') {
            $union = $union ? $union->unionAll($this->systemQuery()) : $this->systemQuery();
        }

        $query = DB::query()->fromSub($union, 'u');

        if (! empty($filters['from'])) {
            $query->where('paid_at', '>=', $filters['from'] . ' 00:00:00');
        }
        if (! empty($filters['to'])) {
            $query->where('paid_at', '<=', $filters['to'] . ' 23:59:59');
        }
        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (! empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }
        if (! empty($filters['method'])) {
            $query->where('method_key', $filters['method']);
        }
        if (! empty($filters['purpose_id'])) {
            $query->where('purpose_id', $filters['purpose_id']);
        }
        if (! empty($filters['type'])) {
            $query->where('kind', $filters['type']);
        }
        if (filled($filters['search'] ?? null)) {
            $query->whereIn('user_id', $this->patientIdsMatching($filters['search']));
        }

        return $query;
    }

    private function totalsRow(array $filters): object
    {
        return $this->query($filters)->selectRaw("
                COUNT(*) as rows_count,
                COUNT(DISTINCT user_id) as patients,
                COALESCE(SUM(amount), 0) as net,
                COALESCE(SUM(CASE WHEN source = 'system' THEN amount ELSE 0 END), 0) as system_total,
                COALESCE(SUM(CASE WHEN source = 'manual' AND kind = 'payment' THEN amount ELSE 0 END), 0) as manual_total,
                COALESCE(SUM(CASE WHEN kind = 'refund' THEN -amount ELSE 0 END), 0) as refund_total
            ")->first();
    }

    private function formatTotals(object $row): object
    {
        return (object) [
            'rows' => (int) $row->rows_count,
            'patients' => (int) $row->patients,
            'net' => (int) $row->net,
            'system' => (int) $row->system_total,
            'manual' => (int) $row->manual_total,
            'refund' => (int) $row->refund_total,
        ];
    }

    /** only the totals: cheap enough for the list pages */
    public function totals(array $filters = []): object
    {
        return $this->formatTotals($this->totalsRow($filters));
    }

    /** totals and the breakdowns shown in the reports */
    public function summary(array $filters = [], int $topPatients = 10): array
    {
        $base = fn () => $this->query($filters);

        $totals = $this->totalsRow($filters);

        $methodLabels = FinancePaymentMethod::reportLabels();
        $byMethod = $base()->selectRaw('method_key, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('method_key')->orderByDesc('total')->get()
            ->map(fn ($r) => (object) ['key' => $r->method_key, 'label' => $methodLabels[$r->method_key] ?? $r->method_key, 'count' => (int) $r->count, 'total' => (int) $r->total]);

        $purposeTitles = FinancePaymentPurpose::pluck('title', 'id');
        $byPurpose = $base()->selectRaw('purpose_id, source, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('purpose_id', 'source')->orderByDesc('total')->get()
            ->map(fn ($r) => (object) [
                'key' => $r->purpose_id,
                'label' => $r->purpose_id
                    ? ($purposeTitles[$r->purpose_id] ?? 'نامشخص')
                    : ($r->source === 'system' ? 'پرداخت سیستمی (نوبت)' : 'ثبت دستی بدون دلیل مشخص'),
                'count' => (int) $r->count,
                'total' => (int) $r->total,
            ]);

        $byDay = $base()->selectRaw('DATE(paid_at) as day, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('day')->orderBy('day')->get()
            ->map(fn ($r) => (object) ['day' => $r->day, 'count' => (int) $r->count, 'total' => (int) $r->total]);

        $doctorRows = $base()->selectRaw('doctor_id, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('doctor_id')->orderByDesc('total')->get();
        $doctors = User::whereIn('id', $doctorRows->pluck('doctor_id')->filter())->get()->keyBy('id');
        $byDoctor = $doctorRows->map(fn ($r) => (object) [
            'key' => $r->doctor_id,
            'label' => $r->doctor_id ? ($doctors[$r->doctor_id]?->fullName ?: 'نامشخص') : 'بدون پزشک',
            'count' => (int) $r->count,
            'total' => (int) $r->total,
        ]);

        $topRows = $base()->selectRaw('user_id, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('user_id')->orderByDesc('total')->limit($topPatients)->get();
        $patients = User::whereIn('id', $topRows->pluck('user_id'))->get()->keyBy('id');
        $top = $topRows->map(fn ($r) => (object) [
            'user_id' => $r->user_id,
            'name' => $patients[$r->user_id]?->fullName ?: ($patients[$r->user_id]?->mobile ?? 'کاربر حذف شده'),
            'count' => (int) $r->count,
            'total' => (int) $r->total,
        ]);

        return [
            'totals' => $this->formatTotals($totals),
            'byMethod' => $byMethod,
            'byPurpose' => $byPurpose,
            'byDay' => $byDay,
            'byDoctor' => $byDoctor,
            'topPatients' => $top,
        ];
    }

    /** one line per patient: how much was paid, in how many payments and when the last one was */
    public function patientsQuery(array $filters = []): Builder
    {
        return $this->query($filters)->selectRaw("
                user_id,
                COUNT(*) as payments_count,
                COALESCE(SUM(CASE WHEN source = 'system' THEN amount ELSE 0 END), 0) as system_total,
                COALESCE(SUM(CASE WHEN source = 'manual' AND kind = 'payment' THEN amount ELSE 0 END), 0) as manual_total,
                COALESCE(SUM(CASE WHEN kind = 'refund' THEN -amount ELSE 0 END), 0) as refund_total,
                COALESCE(SUM(amount), 0) as net,
                MAX(paid_at) as last_paid_at
            ")->groupBy('user_id');
    }
}
