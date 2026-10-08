<?php

namespace Modules\Finance\Livewire;

use Hekmatinasser\Verta\Verta;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Finance\Livewire\Concerns\InteractsWithFinanceFilters;
use Modules\Finance\Services\FinanceReportService;

#[Title('داشبورد مالی')]
class FinanceDashboard extends Component
{
    use InteractsWithFinanceFilters, WithPagination;

    #[Url]
    public array $filter = [];

    public function mount(): void
    {
        $this->filter = $this->filter + $this->defaultFilter();
    }

    protected function defaultFilter(): array
    {
        $today = Verta::now();

        return [
            'from' => $today->copy()->startMonth()->format('Y/m/d'),
            'to' => $today->format('Y/m/d'),
            'doctor_id' => '',
            'source' => '',
        ];
    }

    #[On('payment-saved')]
    public function refreshDashboard(): void
    {
        // the numbers are calculated again when the component renders
    }

    public function render()
    {
        $summary = app(FinanceReportService::class)->summary($this->serviceFilters());

        return view('finance::livewire.finance-dashboard', [
            'summary' => $summary,
            'options' => $this->filterOptions(),
            'maxDay' => max(1, (int) $summary['byDay']->max('total')),
            'canCreate' => $this->can('finance.create'),
        ]);
    }
}
