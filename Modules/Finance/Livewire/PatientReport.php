<?php

namespace Modules\Finance\Livewire;

use Hekmatinasser\Verta\Verta;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Finance\Exports\PatientsExport;
use Modules\Finance\Livewire\Concerns\InteractsWithFinanceFilters;
use Modules\Finance\Services\FinanceReportService;
use Modules\User\Entities\User;

#[Title('گزارش مالی بیماران')]
class PatientReport extends Component
{
    use InteractsWithFinanceFilters, WithPagination;

    public const SORTS = ['net', 'payments_count', 'system_total', 'manual_total', 'refund_total', 'last_paid_at'];

    #[Url]
    public array $filter = [];

    #[Url]
    public string $sort = 'net';

    #[Url]
    public string $direction = 'desc';

    public function mount(): void
    {
        $this->filter = $this->filter + $this->defaultFilter();
    }

    protected function defaultFilter(): array
    {
        return ['from' => '', 'to' => '', 'search' => '', 'doctor_id' => '', 'method' => '', 'purpose_id' => '', 'source' => ''];
    }

    public function sortBy(string $column): void
    {
        if (! in_array($column, self::SORTS, true)) {
            return;
        }
        $this->direction = ($this->sort === $column && $this->direction === 'desc') ? 'asc' : 'desc';
        $this->sort = $column;
        $this->resetPage();
    }

    private function orderedQuery()
    {
        $sort = in_array($this->sort, self::SORTS, true) ? $this->sort : 'net';

        return app(FinanceReportService::class)->patientsQuery($this->serviceFilters())
            ->orderBy($sort, $this->direction === 'asc' ? 'asc' : 'desc')->orderBy('user_id');
    }

    private function decorate($rows)
    {
        $users = User::whereIn('id', $rows->pluck('user_id'))->get()->keyBy('id');

        return $rows->map(function ($row) use ($users) {
            $user = $users[$row->user_id] ?? null;
            $row->name = $user ? ($user->fullName ?: $user->mobile) : 'کاربر حذف شده';
            $row->mobile = $user?->mobile;

            return $row;
        });
    }

    public function export()
    {
        $this->authorizeFinance('finance.export');

        return Excel::download(new PatientsExport($this->decorate($this->orderedQuery()->limit(20000)->get())), 'patients-finance-' . Verta::now()->format('Y-m-d') . '.xlsx');
    }

    public function render()
    {
        $paginator = $this->orderedQuery()->paginate(20);
        $paginator->setCollection($this->decorate($paginator->getCollection()));

        return view('finance::livewire.patient-report', [
            'patients' => $paginator,
            'totals' => app(FinanceReportService::class)->totals($this->serviceFilters()),
            'options' => $this->filterOptions(),
            'canExport' => $this->can('finance.export'),
        ]);
    }
}
