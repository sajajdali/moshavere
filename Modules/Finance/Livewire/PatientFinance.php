<?php

namespace Modules\Finance\Livewire;

use Hekmatinasser\Verta\Verta;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\Finance\app\Models\FinancePayment;
use Modules\Finance\Exports\PaymentsExport;
use Modules\Finance\Livewire\Concerns\InteractsWithFinanceFilters;
use Modules\Finance\Services\FinanceReportService;
use Modules\User\Entities\User;

#[Title('وضعیت مالی بیمار')]
class PatientFinance extends Component
{
    use InteractsWithFinanceFilters, WithPagination;

    public User $user;

    public array $filter = [];

    /** shown as a tab of the patient file instead of a page of its own */
    public bool $embedded = false;

    public function mount(User $user, bool $embedded = false): void
    {
        $this->user = $user;
        $this->embedded = $embedded;
        $this->filter = $this->filter + $this->defaultFilter();
    }

    protected function defaultFilter(): array
    {
        return ['from' => '', 'to' => '', 'method' => '', 'purpose_id' => '', 'source' => '', 'type' => ''];
    }

    #[On('payment-saved')]
    public function refreshList(): void
    {
        // the page is rendered again
    }

    public function deletePayment(int $id): void
    {
        $this->authorizeFinance('finance.delete');
        FinancePayment::where('user_id', $this->user->id)->findOrFail($id)->delete();
        $this->dispatch('showAlert', message: 'پرداخت حذف شد');
    }

    public function export()
    {
        $this->authorizeFinance('finance.export');
        $rows = $this->decorateRows(app(FinanceReportService::class)->query($this->serviceFilters(['user_id' => $this->user->id]))->orderByDesc('paid_at')->get());

        return Excel::download(new PaymentsExport($rows), 'patient-' . $this->user->id . '-payments-' . Verta::now()->format('Y-m-d') . '.xlsx');
    }

    public function render()
    {
        $service = app(FinanceReportService::class);
        $filters = $this->serviceFilters(['user_id' => $this->user->id]);
        $paginator = $service->query($filters)->orderByDesc('paid_at')->orderByDesc('row_id')->paginate(15);
        $paginator->setCollection($this->decorateRows($paginator->getCollection()));

        // the totals of the whole file, not only of the filtered period
        $allTime = ['totals' => $service->totals(['user_id' => $this->user->id])];

        return view('finance::livewire.patient-finance', [
            'payments' => $paginator,
            'summary' => $service->summary($filters, 0),
            'allTime' => $allTime['totals'],
            'options' => $this->filterOptions(),
            'appointmentsCount' => AppointmentUser::where('user_id', $this->user->id)->count(),
            'canCreate' => $this->can('finance.create'),
            'canEdit' => $this->can('finance.edit'),
            'canDelete' => $this->can('finance.delete'),
            'canExport' => $this->can('finance.export'),
        ]);
    }
}
