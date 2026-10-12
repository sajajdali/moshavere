<?php

namespace Modules\AppointmentUser\Livewire\Admin;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\Transaction\app\Models\Transaction;
use Modules\Transaction\Enum\TransactionPaidEnum;
use Modules\Transaction\Enum\TransactionStatusEnum;
use Modules\User\Entities\User;

/**
 * گزارش عمومی نوبت دهی (فقط مدیر کل).
 * تجمیع ها در دیتابیس به تفکیک روز انجام میشود و تبدیل به هفته/ماه شمسی در PHP.
 */
#[Title('گزارش عمومی نوبت دهی')]
class AppointmentReport extends Component
{
    // فیلترها در آدرس صفحه نگه داشته میشوند تا گزارش قابل اشتراک و بعد از بارگذاری مجدد ثابت بماند
    #[Url(except: '')]
    public string $doctorId = '';
    // '' همه | patients خود بیماران (سایت و ویپ) | staff همه منشی ها و مدیران | شناسه ثبت کننده
    #[Url(except: '')]
    public string $registrar = '';
    #[Url(as: 'from', except: '')]
    public string $dateFrom = '';
    #[Url(as: 'to', except: '')]
    public string $dateTo = '';
    #[Url]
    public string $preset = '90d';
    // created تاریخ ثبت نوبت | visit تاریخ نوبت
    #[Url(as: 'basis', except: 'created')]
    public string $dateBasis = 'created';
    #[Url(as: 'group')]
    public string $granularity = 'week';

    private const SOURCE_LABELS = [
        'self' => 'سایت (خود بیمار)',
        'staff' => 'منشی / مدیریت',
        'voip' => 'تلفن (ویپ)',
        'import' => 'ایمپورت شده',
    ];

    private const PRESETS = ['7d', '30d', '90d', '6m', 'year', '1y', 'all'];

    private const DAILY_CHART_MAX_DAYS = 180;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('SUPER_ADMIN'), 403);

        // مقادیر آدرس صفحه پیش از mount اعمال شده اند؛ مقدار نامعتبر به پیش فرض برمیگردد
        if (! in_array($this->dateBasis, ['created', 'visit'], true)) {
            $this->dateBasis = 'created';
        }
        $granularity = in_array($this->granularity, ['day', 'week', 'month'], true) ? $this->granularity : null;

        // بازه های آماده همیشه نسبت به امروز محاسبه میشوند؛ فقط بازه دلخواه تاریخ ثابت دارد
        if (in_array($this->preset, self::PRESETS, true) || $this->dateFrom === '' || $this->dateTo === '') {
            $this->applyPreset(in_array($this->preset, self::PRESETS, true) ? $this->preset : '90d');
        }
        if ($granularity && request()->query('group')) {
            $this->granularity = $granularity;
        }
    }

    public function applyPreset(string $preset): void
    {
        if (! in_array($preset, self::PRESETS, true)) {
            return;
        }

        $today = $preset === 'all' ? max(now(), $this->lastVisitDate() ?? now())->endOfDay() : now()->endOfDay();
        $from = match ($preset) {
            '7d' => now()->subDays(6),
            '30d' => now()->subDays(29),
            '90d' => now()->subDays(89),
            '6m' => now()->subMonths(6)->addDay(),
            'year' => verta()->startYear()->toCarbon(),
            '1y' => now()->subYear()->addDay(),
            'all' => $this->firstRecordDate() ?? now()->subYear(),
        };

        $this->preset = $preset;
        $this->dateFrom = verta($from)->format('Y/m/d');
        $this->dateTo = verta($today)->format('Y/m/d');
        $this->granularity = match ($preset) {
            '7d', '30d' => 'day',
            '90d', '6m' => 'week',
            default => 'month',
        };
    }

    public function updatedDateFrom(): void
    {
        $this->preset = 'custom';
    }

    public function updatedDateTo(): void
    {
        $this->preset = 'custom';
    }

    public function setGranularity(string $granularity): void
    {
        if (in_array($granularity, ['day', 'week', 'month'], true)) {
            $this->granularity = $granularity;
        }
    }

    public function setDateBasis(string $basis): void
    {
        if (in_array($basis, ['created', 'visit'], true)) {
            $this->dateBasis = $basis;
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['doctorId', 'registrar', 'dateBasis']);
        $this->applyPreset('90d');
    }

    public function render()
    {
        abort_unless(auth()->user()?->can('SUPER_ADMIN'), 403);

        [$from, $to] = $this->range();
        $report = $this->buildReport($from, $to);

        $this->dispatch('appointment-report-updated', report: $report['charts']);

        return view('appointmentuser::livewire.admin.appointment-report', [
            'report' => $report,
            'from' => $from,
            'to' => $to,
            'doctors' => $this->doctorOptions(),
            'registrars' => $this->registrarOptions(),
        ]);
    }

    private function buildReport(Carbon $from, Carbon $to): array
    {
        $appointments = $this->appointmentRows($from, $to);
        $payments = $this->paymentRows($from, $to);

        $names = $this->userNames(
            $appointments->pluck('doctor_id')
                ->merge($payments->pluck('doctor_id'))
                ->merge($appointments->pluck('reg'))
                ->merge($payments->pluck('reg'))
        );

        $cancelStatuses = [
            AppointmentUserStatusEnum::STATUS_CANCEL->value,
            AppointmentUserStatusEnum::STATUS_DISAPPROVED->value,
            AppointmentUserStatusEnum::STATUS_ONILNE_CLOSED->value,
        ];

        // ---------- KPI ----------
        $total = (int) $appointments->sum('c');
        $bySource = $appointments->groupBy('src')->map(fn ($g) => (int) $g->sum('c'));
        $canceled = (int) $appointments->whereIn('status', $cancelStatuses)->sum('c');
        $attended = (int) $appointments->where('status', AppointmentUserStatusEnum::STATUS_ATTENDED->value)->sum('c');
        $paySum = (int) $payments->sum('s');
        $payCount = (int) $payments->sum('c');

        $previous = $this->previousPeriodTotals($from, $to);

        $kpi = [
            'total' => $total,
            'total_change' => $this->change($total, $previous['appointments']),
            'self' => $bySource->get('self', 0),
            'voip' => $bySource->get('voip', 0),
            'staff' => $bySource->get('staff', 0) + $bySource->get('import', 0),
            'canceled' => $canceled,
            'cancel_rate' => $total ? round($canceled * 100 / $total, 1) : 0,
            'attended' => $attended,
            'pay_sum' => $paySum,
            'pay_change' => $this->change($paySum, $previous['payments']),
            'pay_count' => $payCount,
            'pay_avg' => $payCount ? (int) round($paySum / $payCount) : 0,
            'patients' => $this->uniquePatients($from, $to),
            'doctors' => $appointments->pluck('doctor_id')->filter()->unique()->count(),
            'previous_label' => verta($previous['from'])->format('Y/m/d').' تا '.verta($previous['to'])->format('Y/m/d'),
        ];

        // ---------- روند نوبت ها ----------
        $buckets = $this->buckets($from, $to, $this->granularity);
        $trend = ['labels' => array_values($buckets)];
        foreach (['self', 'staff', 'voip'] as $source) {
            $trend[$source] = $this->series($appointments->filter(fn ($r) => $this->sourceGroup($r->src) === $source), $buckets, 'c', $this->granularity);
        }
        $trend['canceled'] = $this->series($appointments->whereIn('status', $cancelStatuses), $buckets, 'c', $this->granularity);

        // ---------- پزشکان ----------
        $doctorRows = $appointments->groupBy('doctor_id')->map(function (Collection $rows, $doctorId) use ($payments, $names, $cancelStatuses) {
            $doctorPayments = $payments->where('doctor_id', $doctorId);

            return [
                'id' => $doctorId,
                'name' => $names[$doctorId] ?? 'نامشخص',
                'total' => (int) $rows->sum('c'),
                'self' => (int) $rows->filter(fn ($r) => $this->sourceGroup($r->src) === 'self')->sum('c'),
                'voip' => (int) $rows->where('src', 'voip')->sum('c'),
                'staff' => (int) $rows->filter(fn ($r) => $this->sourceGroup($r->src) === 'staff')->sum('c'),
                'canceled' => (int) $rows->whereIn('status', $cancelStatuses)->sum('c'),
                'attended' => (int) $rows->where('status', AppointmentUserStatusEnum::STATUS_ATTENDED->value)->sum('c'),
                'pay_sum' => (int) $doctorPayments->sum('s'),
                'pay_count' => (int) $doctorPayments->sum('c'),
            ];
        });
        // پزشکی که در بازه فقط پرداخت دارد (نوبت در بازه دیگری ثبت شده)
        foreach ($payments->groupBy('doctor_id') as $doctorId => $rows) {
            if (! $doctorRows->has($doctorId)) {
                $doctorRows->put($doctorId, [
                    'id' => $doctorId, 'name' => $names[$doctorId] ?? 'نامشخص',
                    'total' => 0, 'self' => 0, 'voip' => 0, 'staff' => 0, 'canceled' => 0, 'attended' => 0,
                    'pay_sum' => (int) $rows->sum('s'), 'pay_count' => (int) $rows->sum('c'),
                ]);
            }
        }
        $doctorRows = $doctorRows->sortByDesc('total')->values();
        $topDoctors = $doctorRows->take(15);
        $topDoctorPayments = $doctorRows->where('pay_sum', '>', 0)->sortByDesc('pay_sum')->take(15)->values();

        // ---------- ثبت کنندگان ----------
        $registrarRows = $appointments->filter(fn ($r) => $this->sourceGroup($r->src) === 'staff')
            ->groupBy(fn ($r) => (string) ($r->reg ?? 0))
            ->map(fn (Collection $rows, $id) => [
                'id' => (int) $id,
                'name' => $id ? ($names[$id] ?? 'نامشخص') : 'نامشخص',
                'total' => (int) $rows->sum('c'),
                'canceled' => (int) $rows->whereIn('status', $cancelStatuses)->sum('c'),
                'doctors' => $rows->pluck('doctor_id')->filter()->unique()->count(),
                'pay_sum' => (int) $payments->where('reg', $id ?: null)->where('src_group', 'staff')->sum('s'),
            ])
            ->sortByDesc('total')->values();

        // ---------- پرداخت ها ----------
        $dailyFrom = $from->diffInDays($to) >= self::DAILY_CHART_MAX_DAYS
            ? $to->copy()->subDays(self::DAILY_CHART_MAX_DAYS - 1)->startOfDay()
            : $from;
        $dayBuckets = $this->buckets($dailyFrom, $to, 'day');
        $monthBuckets = $this->buckets($from, $to, 'month');
        $monthSums = $this->series($payments, $monthBuckets, 's', 'month');
        $cumulative = [];
        $running = 0;
        foreach ($monthSums as $sum) {
            $running += $sum;
            $cumulative[] = $running;
        }
        $dailySums = $this->series($payments, $dayBuckets, 's', 'day');
        $dailyCumulative = [];
        $running = 0;
        foreach ($dailySums as $sum) {
            $running += $sum;
            $dailyCumulative[] = $running;
        }

        // ---------- جدول ماهانه ----------
        $monthAppointments = $this->series($appointments, $monthBuckets, 'c', 'month');
        $monthSelf = $this->series($appointments->filter(fn ($r) => $this->sourceGroup($r->src) === 'self'), $monthBuckets, 'c', 'month');
        $monthStaff = $this->series($appointments->filter(fn ($r) => $this->sourceGroup($r->src) === 'staff'), $monthBuckets, 'c', 'month');
        $monthVoip = $this->series($appointments->where('src', 'voip'), $monthBuckets, 'c', 'month');
        $monthPayCount = $this->series($payments, $monthBuckets, 'c', 'month');
        $monthly = [];
        foreach (array_values($monthBuckets) as $i => $label) {
            $monthly[] = [
                'label' => $label,
                'total' => $monthAppointments[$i],
                'self' => $monthSelf[$i],
                'staff' => $monthStaff[$i],
                'voip' => $monthVoip[$i],
                'pay_count' => $monthPayCount[$i],
                'pay_sum' => $monthSums[$i],
                'cumulative' => $cumulative[$i],
            ];
        }

        // ---------- توزیع ها ----------
        $statusCounts = $appointments->groupBy('status')->map(fn ($g) => (int) $g->sum('c'));
        $status = collect(AppointmentUserStatusEnum::cases())
            ->filter(fn ($case) => $statusCounts->get($case->value, 0) > 0)
            ->map(fn ($case) => [
                'label' => $case->getName(),
                'count' => $statusCounts->get($case->value, 0),
                'color' => $this->statusColor($case),
            ])->values();

        $weekdayNames = ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه'];
        $weekday = array_fill(0, 7, 0);
        foreach ($appointments as $row) {
            // Carbon: 0=یکشنبه ... 6=شنبه؛ هفته شمسی از شنبه شروع میشود
            $weekday[(Carbon::parse($row->d)->dayOfWeek + 1) % 7] += (int) $row->c;
        }

        $paidBy = $payments->groupBy('paid_by')->map(fn ($g, $paidBy) => [
            'label' => $this->paidByLabel((int) $paidBy),
            'sum' => (int) $g->sum('s'),
            'count' => (int) $g->sum('c'),
        ])->sortByDesc('sum')->values();

        $sources = collect(['self', 'staff', 'voip', 'import'])
            ->map(fn ($s) => ['label' => self::SOURCE_LABELS[$s], 'count' => $bySource->get($s, 0)])
            ->filter(fn ($s) => $s['count'] > 0)->values();

        return [
            'kpi' => $kpi,
            'doctors' => $doctorRows,
            'registrars' => $registrarRows,
            'monthly' => $monthly,
            'paid_by' => $paidBy,
            'daily_truncated' => $dailyFrom->gt($from),
            'charts' => [
                'trend' => $trend,
                'doctors' => [
                    'labels' => $topDoctors->pluck('name')->values(),
                    'self' => $topDoctors->pluck('self')->values(),
                    'staff' => $topDoctors->pluck('staff')->values(),
                    'voip' => $topDoctors->pluck('voip')->values(),
                ],
                'doctorPayments' => [
                    'labels' => $topDoctorPayments->pluck('name')->values(),
                    'sums' => $topDoctorPayments->pluck('pay_sum')->values(),
                ],
                'registrars' => [
                    'labels' => $registrarRows->take(15)->pluck('name')->values(),
                    'counts' => $registrarRows->take(15)->pluck('total')->values(),
                ],
                'sources' => [
                    'labels' => $sources->pluck('label'),
                    'counts' => $sources->pluck('count'),
                ],
                'status' => [
                    'labels' => $status->pluck('label'),
                    'counts' => $status->pluck('count'),
                    'colors' => $status->pluck('color'),
                ],
                'weekday' => ['labels' => $weekdayNames, 'counts' => $weekday],
                'payDaily' => [
                    'labels' => array_values($dayBuckets),
                    'sums' => $dailySums,
                    'cumulative' => $dailyCumulative,
                ],
                'payMonthly' => [
                    'labels' => array_values($monthBuckets),
                    'sums' => $monthSums,
                    'counts' => $monthPayCount,
                    'cumulative' => $cumulative,
                ],
                'paidBy' => [
                    'labels' => $paidBy->pluck('label'),
                    'sums' => $paidBy->pluck('sum'),
                ],
            ],
        ];
    }

    /**
     * تعداد نوبت ها به تفکیک روز، پزشک، منبع ثبت، ثبت کننده، وضعیت و نوع.
     */
    private function appointmentRows(Carbon $from, Carbon $to): Collection
    {
        $column = $this->dateColumn();
        $source = $this->sourceSql();

        return $this->appointmentQuery($from, $to)
            ->selectRaw("DATE({$column}) as d, doctor_id, {$source} as src, CASE WHEN ({$source}) IN ('staff', 'import') THEN agent_id END as reg, status, COUNT(*) as c")
            ->groupByRaw('d, doctor_id, src, reg, status')
            ->get();
    }

    /**
     * پرداخت های موفق نوبت ها (بر اساس تاریخ پرداخت) به تفکیک روز، پزشک، روش پرداخت و ثبت کننده.
     */
    private function paymentRows(Carbon $from, Carbon $to): Collection
    {
        $prefix = DB::connection()->getTablePrefix();
        $source = $this->sourceSql('a');

        return $this->paymentQuery($from, $to)
            ->selectRaw("DATE({$prefix}t.created_at) as d, {$prefix}a.doctor_id as doctor_id, {$prefix}t.paid_by as paid_by, {$source} as src, CASE WHEN ({$source}) IN ('staff', 'import') THEN {$prefix}a.agent_id END as reg, SUM({$prefix}t.total_cost) as s, COUNT(*) as c")
            ->groupByRaw('d, doctor_id, paid_by, src, reg')
            ->get()
            ->each(fn ($row) => $row->src_group = $this->sourceGroup($row->src));
    }

    private function appointmentQuery(Carbon $from, Carbon $to)
    {
        $query = AppointmentUser::query()
            ->toBase()
            ->whereBetween($this->dateColumn(), [$from, $to])
            ->when($this->doctorId !== '', fn ($q) => $q->where('doctor_id', (int) $this->doctorId));

        return $this->applyRegistrarFilter($query, $this->sourceSql(), 'agent_id');
    }

    private function paymentQuery(Carbon $from, Carbon $to)
    {
        $appointment = new AppointmentUser;

        // پرداختی که نوبتش دیگر وجود ندارد هم در مجموع پرداختی ها حساب میشود (پزشک «نامشخص»)
        $query = DB::table((new Transaction)->getTable().' as t')
            ->leftJoin($appointment->getTable().' as a', 'a.id', '=', 't.transactionable_id')
            ->where('t.transactionable_type', $appointment->getMorphClass())
            ->where('t.status', TransactionStatusEnum::SUCCESSFUL->value)
            ->where('t.total_cost', '>', 0)
            ->whereBetween('t.created_at', [$from, $to])
            ->when($this->doctorId !== '', fn ($q) => $q->where('a.doctor_id', (int) $this->doctorId));

        if ($this->registrar !== '') {
            $query->whereNotNull('a.id');
        }

        return $this->applyRegistrarFilter($query, $this->sourceSql('a'), 'a.agent_id');
    }

    private function applyRegistrarFilter($query, string $source, string $agentColumn)
    {
        return match (true) {
            $this->registrar === 'patients' => $query->whereRaw("({$source}) IN ('self', 'voip')"),
            $this->registrar === 'staff' => $query->whereRaw("({$source}) IN ('staff', 'import')"),
            ctype_digit($this->registrar) => $query->whereRaw("({$source}) IN ('staff', 'import')")
                ->where($agentColumn, (int) $this->registrar),
            default => $query,
        };
    }

    /**
     * منبع ثبت نوبت، هم ارز AppointmentUser::registrationSource():
     * ابتدا منبع ذخیره شده در جزئیات، وگرنه اگر ثبت کننده کسی غیر از خود بیمار باشد «منشی/مدیریت».
     */
    private function sourceSql(string $table = ''): string
    {
        $column = fn (string $name) => $table === ''
            ? $name
            : DB::connection()->getTablePrefix().$table.'.'.$name;
        $details = $column('details');
        $agent = $column('agent_id');
        $user = $column('user_id');
        $via = fn (int $value) => "({$details} LIKE '%\"appointment_via\":{$value}%' OR {$details} LIKE '%\"appointment_via\": {$value}%')";
        $voip = $via(4);
        $byAdmin = $via(2);
        $import = $via(3);
        $self = $via(1);

        return "(CASE WHEN {$voip} THEN 'voip' WHEN {$byAdmin} THEN 'staff' WHEN {$import} THEN 'import' WHEN {$self} THEN 'self'"
            ." WHEN {$agent} IS NOT NULL AND {$agent} <> {$user} THEN 'staff' ELSE 'self' END)";
    }

    private function sourceGroup(?string $source): string
    {
        return match ($source) {
            'staff', 'import' => 'staff',
            'voip' => 'voip',
            default => 'self',
        };
    }

    private function previousPeriodTotals(Carbon $from, Carbon $to): array
    {
        $days = $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1;
        $prevTo = $from->copy()->subDay()->endOfDay();
        $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();

        return [
            'from' => $prevFrom,
            'to' => $prevTo,
            'appointments' => (int) $this->appointmentQuery($prevFrom, $prevTo)->count(),
            'payments' => (int) $this->paymentQuery($prevFrom, $prevTo)->sum('t.total_cost'),
        ];
    }

    private function uniquePatients(Carbon $from, Carbon $to): int
    {
        return (int) $this->appointmentQuery($from, $to)->distinct()->count('user_id');
    }

    private function change(int $current, int $previous): ?float
    {
        if ($previous === 0) {
            return null;
        }

        return round(($current - $previous) * 100 / $previous, 1);
    }

    /**
     * بازه های نمودار (روز، هفته شمسی از شنبه، ماه شمسی) به ترتیب زمانی: کلید => برچسب
     */
    private function buckets(Carbon $from, Carbon $to, string $granularity): array
    {
        $buckets = [];
        $multiYear = verta($from)->year !== verta($to)->year;
        for ($day = $from->copy()->startOfDay(); $day->lte($to); $day->addDay()) {
            $key = $this->bucketKey($day, $granularity);
            if (isset($buckets[$key])) {
                continue;
            }
            $label = match ($granularity) {
                'day' => verta($day)->format($multiYear ? 'j F y' : 'j F'),
                'week' => verta(Carbon::parse($key))->format($multiYear ? 'j F y' : 'j F'),
                default => verta($day)->format('F Y'),
            };
            $buckets[$key] = strtr($label, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
        }

        return $buckets;
    }

    private function bucketKey(Carbon $date, string $granularity): string
    {
        return match ($granularity) {
            'day' => $date->toDateString(),
            'week' => $date->copy()->startOfWeek(Carbon::SATURDAY)->toDateString(),
            default => verta($date)->format('Y-m'),
        };
    }

    /**
     * جمع ستون $field هر ردیف روزانه در بازه مربوطش.
     */
    private function series(Collection $rows, array $buckets, string $field, string $granularity): array
    {
        $values = array_fill_keys(array_keys($buckets), 0);
        foreach ($rows as $row) {
            $key = $this->bucketKey(Carbon::parse($row->d), $granularity);
            if (array_key_exists($key, $values)) {
                $values[$key] += (int) $row->{$field};
            }
        }

        return array_values($values);
    }

    private function range(): array
    {
        $from = $this->parseDate($this->dateFrom) ?? now()->subDays(89);
        $to = $this->parseDate($this->dateTo) ?? now();
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from->copy()->startOfDay(), $to->copy()->endOfDay()];
    }

    private function dateColumn(): string
    {
        return $this->dateBasis === 'visit' ? 'date_visit' : 'created_at';
    }

    /**
     * قدیمی ترین تاریخ ثبت، نوبت یا پرداخت؛ برای بازه «همه»
     */
    private function firstRecordDate(): ?Carbon
    {
        $dates = collect([
            AppointmentUser::query()->min('created_at'),
            AppointmentUser::query()->min('date_visit'),
            $this->paymentQuery(Carbon::create(2000), now()->addYears(10))->min('t.created_at'),
        ])->filter()->map(fn ($date) => Carbon::parse($date));

        return $dates->isEmpty() ? null : $dates->min();
    }

    private function lastVisitDate(): ?Carbon
    {
        $last = AppointmentUser::query()->max('date_visit');

        return $last ? Carbon::parse($last) : null;
    }

    private function userNames(Collection $ids): array
    {
        $ids = $ids->filter()->map(fn ($id) => (int) $id)->unique()->values();
        if ($ids->isEmpty()) {
            return [];
        }

        return User::query()->whereIn('id', $ids)->get()
            ->mapWithKeys(fn ($user) => [$user->id => trim((string) $user->full_name) ?: ('کاربر #'.$user->id)])
            ->all();
    }

    private function doctorOptions(): Collection
    {
        $ids = AppointmentUser::query()->whereNotNull('doctor_id')->distinct()->pluck('doctor_id');

        return collect($this->userNames($ids))->sort();
    }

    private function registrarOptions(): Collection
    {
        $source = $this->sourceSql();
        $ids = AppointmentUser::query()
            ->whereRaw("({$source}) IN ('staff', 'import')")
            ->whereNotNull('agent_id')
            ->distinct()
            ->pluck('agent_id');

        return collect($this->userNames($ids))->sort();
    }

    private function statusColor(AppointmentUserStatusEnum $status): string
    {
        return match ($status) {
            AppointmentUserStatusEnum::STATUS_PENDING => '#f79009',
            AppointmentUserStatusEnum::STATUS_SUCCESSFUL => '#12b76a',
            AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT => '#0ba5ec',
            AppointmentUserStatusEnum::STATUS_CANCEL => '#f04438',
            AppointmentUserStatusEnum::STATUS_ATTENDED => '#6172f3',
            AppointmentUserStatusEnum::STATUS_NOT_ATTENDED => '#98a2b3',
            AppointmentUserStatusEnum::STATUS_DISAPPROVED => '#b42318',
            AppointmentUserStatusEnum::STATUS_MONITORING => '#fdb022',
            AppointmentUserStatusEnum::STATUS_ONILNE_CLOSED => '#7a5af8',
        };
    }

    private function paidByLabel(int $paidBy): string
    {
        return match (TransactionPaidEnum::tryFrom($paidBy)) {
            TransactionPaidEnum::ONLINE => 'درگاه آنلاین',
            TransactionPaidEnum::CARD_TO_CARD => 'کارت به کارت',
            TransactionPaidEnum::BY_ADMIN => 'ثبت توسط مدیریت',
            TransactionPaidEnum::WALLET => 'کیف پول',
            TransactionPaidEnum::NO_NEED_TO_PAY => 'بدون نیاز به پرداخت',
            default => 'سایر',
        };
    }

    private function parseDate(string $value): ?Carbon
    {
        $value = trim(strtr($value, ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9']));
        if ($value === '') {
            return null;
        }
        try {
            return \Verta::parse($value)->toCarbon();
        } catch (\Throwable) {
            return null;
        }
    }
}
