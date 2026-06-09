<?php

namespace Modules\Admin\Livewire;
use App\Models\TenantRenewTransaction;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\Transaction\Enum\TransactionPaidEnum;
use Modules\Transaction\Enum\TransactionStatusEnum;
use Stancl\Tenancy\Database\Models\Domain;

#[Title('تمدید هزینه هاست، سرور و پشتیبانی')]
class TenantRenew extends Component
{

    #[Locked]
    public int $totalCost = 7500000;

    public ?string $paymentUrl = null;

    public ?array $transactionResult = null;

    public function mount(): void
    {
        $this->totalCost = config('app.tenant_renew_cost');
        $this->transactionResult = session('tenant_renew_result') ?? $this->getTransactionResultFromRequest();
    }

    public function redirectToBank()
    {
        $user = auth()->user();
        $tenant = tenant();
        $amount = $this->normalizeAmount($this->totalCost);
        $description = sprintf('تمدید اشتراک نوبت دهی توسط %s',$user?->fullName);
        $transaction = TenantRenewTransaction::create([
            'tenant_id' => $tenant->id,
            'tenant_admin_user_id' => $user?->id,
            'tenant_admin_name' => $user?->fullName,
            'tenant_admin_mobile' => $user?->mobile,
            'status' => TransactionStatusEnum::PENDING,
            'paid_by' => TransactionPaidEnum::ONLINE,
            'cost' => $amount,
            'previous_expires_at' => $tenant->expires_at,
            'renewed_until' => $tenant->expires_at?->isFuture()
                ? $tenant->expires_at->copy()->addYear()
                : now()->addYear(),
            'detail' => [
                'description' => $description,
            ],
        ]);
        // dd($this->bankRoute($transaction));
        return redirect()->to($this->bankRoute($transaction));
    }

    public function redirectTransactionToBank(int $transactionId)
    {
        $transaction = TenantRenewTransaction::findOrFail($transactionId);
        abort_if($transaction->tenant_id !== tenant('id'), 403);

        $transaction->update([
            'status' => TransactionStatusEnum::PENDING,
            'paid_at' => null,
        ]);

        return redirect()->to($this->bankRoute($transaction));
    }

    public function render()
    {
        return view('admin::livewire.tenant-renew');
    }

    private function normalizeAmount(mixed $amount): int
    {
        return (int) preg_replace('/\D/', '', (string) $amount);
    }

    private function bankRoute(TenantRenewTransaction $transaction): string
    {
        $tenantDomain = Domain::firstWhere('tenant_id',tenant()->id)?->domain;
        return (string) sprintf(
            'https://maliart.ir/index.php?_route=client/tenant/%s/%d/%s',
            $transaction->id,
            $transaction->cost,
            $tenantDomain,
        );
    }

    private function getTransactionResultFromRequest(): ?array
    {
        $transactionId = request()->integer('transaction_id');

        if ($transactionId === 0) {
            return null;
        }

        $transaction = TenantRenewTransaction::find($transactionId);

        if ($transaction === null || $transaction->tenant_id !== tenant('id')) {
            return null;
        }

        return [
            'status' => $transaction->status->value,
            'message' => $this->messageForStatus($transaction->status),
            'transaction_id' => $transaction->id,
        ];
    }

    private function messageForStatus(TransactionStatusEnum $status): string
    {
        return match ($status) {
            TransactionStatusEnum::SUCCESSFUL => 'پرداخت با موفقیت انجام شد و اشتراک شما تمدید شد.',
            TransactionStatusEnum::PENDING => 'وضعیت پرداخت هنوز در حال انجام است.',
            default => 'پرداخت ناموفق بود. لطفا دوباره تلاش کنید.',
        };
    }
}
