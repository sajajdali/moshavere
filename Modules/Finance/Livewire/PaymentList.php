<?php

namespace Modules\Finance\Livewire;

use Hekmatinasser\Verta\Verta;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Finance\app\Models\FinancePayment;
use Modules\Finance\Exports\PaymentsExport;
use Modules\Finance\Livewire\Concerns\InteractsWithFinanceFilters;
use Modules\Finance\Services\FinanceReportService;

#[Title('پرداخت ها')]
class PaymentList extends Component
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
        return ['from' => '', 'to' => '', 'search' => '', 'doctor_id' => '', 'method' => '', 'purpose_id' => '', 'source' => '', 'type' => ''];
    }

    #[On('payment-saved')]
    public function refreshList(): void
    {
        // the list is rendered again
    }

    /** a manual payment is removed (soft delete): the history stays recoverable by the developers */
    public function deletePayment(int $id): void
    {
        $this->authorizeFinance('finance.delete');
        FinancePayment::findOrFail($id)->delete();
        $this->dispatch('showAlert', message: 'پرداخت حذف شد');
    }

    public function export()
    {
        $this->authorizeFinance('finance.export');
        $rows = $this->decorateRows(app(FinanceReportService::class)->query($this->serviceFilters())->orderByDesc('paid_at')->limit(20000)->get());

        return Excel::download(new PaymentsExport($rows), 'payments-' . Verta::now()->format('Y-m-d') . '.xlsx');
    }

    public function render()
    {
        $service = app(FinanceReportService::class);
        $filters = $this->serviceFilters();
        $paginator = $service->query($filters)->orderByDesc('paid_at')->orderByDesc('row_id')->paginate(20);
        $paginator->setCollection($this->decorateRows($paginator->getCollection()));

        return view('finance::livewire.payment-list', [
            'payments' => $paginator,
            'totals' => $service->totals($filters),
            'options' => $this->filterOptions(),
            'canCreate' => $this->can('finance.create'),
            'canEdit' => $this->can('finance.edit'),
            'canDelete' => $this->can('finance.delete'),
            'canExport' => $this->can('finance.export'),
        ]);
    }
}
