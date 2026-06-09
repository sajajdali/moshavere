<?php

namespace Modules\Admin\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantRenewTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Transaction\Enum\TransactionStatusEnum;

class TenantRenewCallbackController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $this->validateToken($request);

        $data = $request->validate([
            'transaction_id' => ['required', 'integer'],
            'status' => ['required'],
            'reference_id' => ['nullable', 'string', 'max:255'],
            'transaction_code' => ['nullable', 'string', 'max:50'],
            'gateway' => ['nullable', 'string', 'max:255'],
        ]);
        $status = $this->resolveStatus($data['status']);
        $transaction = TenantRenewTransaction::findOrFail($data['transaction_id']);
        abort_if($transaction->tenant_id !== tenant('id'), 403);

        DB::connection(config('tenancy.database.central_connection'))
            ->transaction(function () use ($transaction, $status, $data): void {
                $wasSuccessful = $transaction->status === TransactionStatusEnum::SUCCESSFUL;

                $detail = $transaction->detail ?? [];
                $detail['bank_callback'] = [
                    'status' => $data['status'],
                    'received_at' => now()->toDateTimeString(),
                ];
                $transaction->update([
                    'status' => $status,
                    'reference_id' => $data['reference_id'] ?? $transaction->reference_id,
                    'transaction_code' => $data['transaction_code'] ?? $transaction->transaction_code,
                    'gateway' => $data['gateway'] ?? $transaction->gateway,
                    'paid_at' => $status === TransactionStatusEnum::SUCCESSFUL ? now() : null,
                    'detail' => $detail,
                ]);

                if ($status === TransactionStatusEnum::SUCCESSFUL && ! $wasSuccessful) {
                    $tenant = Tenant::query()
                        ->whereKey($transaction->tenant_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $renewedUntil = $tenant->expires_at?->isFuture()
                        ? $tenant->expires_at->copy()->addYear()
                        : now()->addYear();

                    $tenant->update(['expires_at' => $renewedUntil]);
                    $transaction->update(['renewed_until' => $renewedUntil]);
                }
            });

        return redirect()
            ->route('admin.tenant-renew', ['transaction_id' => $transaction->id])
            ->with('tenant_renew_result', [
                'status' => $status->value,
                'message' => $this->messageForStatus($status),
                'transaction_id' => $transaction->id,
            ]);
    }

    private function validateToken(Request $request): void
    {
        $expectedToken = (string) config('app.tenant_renew_callback_token');
        $token = (string) ($request->input('token') ?: $request->bearerToken());
        abort_if($expectedToken === '' || ! hash_equals($expectedToken, $token), 403);
    }

    private function resolveStatus(mixed $status): TransactionStatusEnum
    {
        if (is_bool($status)) {
            return $status
                ? TransactionStatusEnum::SUCCESSFUL
                : TransactionStatusEnum::REJECTED;
        }

        return match (strtolower((string) $status)) {
            '1', 'success', 'successful', 'paid', 'ok', 'true' => TransactionStatusEnum::SUCCESSFUL,
            'pending' => TransactionStatusEnum::PENDING,
            default   => TransactionStatusEnum::REJECTED,
        };
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
