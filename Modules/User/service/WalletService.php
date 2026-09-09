<?php

namespace Modules\User\service;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\User\app\Models\UserWallet;
use Modules\User\Entities\User;

class WalletService
{
    /**
     * Get current balance from the last ledger row (fast).
     */
    public function balance(User $user): int
    {
        $last = UserWallet::where('user_id', $user->id)
            ->latest('id')
            ->first();

        return (int) ($last?->balance_after ?? 0);
    }

    /**
     * Credit (add) to wallet.
     */
    public function credit(
        User $user,
        int $amount,
        string $type = 'credit',
        ?string $idempotencyKey = null,
        ?array $detail = null,
        ?int $transactionId = null
    ): UserWallet {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Credit amount must be positive.');
        }

        return $this->storeEntry(
            user: $user,
            amountChange: +$amount,
            type: $type,
            idempotencyKey: $idempotencyKey,
            detail: $detail,
            transactionId: $transactionId,
            ensureNotNegative: false
        );
    }

    /**
     * Debit (subtract) from wallet.
     */
    public function debit(
        User $user,
        int $amount,
        string $type = 'purchase',
        ?string $idempotencyKey = null,
        ?array $detail = null,
        ?int $transactionId = null
    ): UserWallet {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Debit amount must be positive.');
        }

        return $this->storeEntry(
            user: $user,
            amountChange: -$amount,
            type: $type,
            idempotencyKey: $idempotencyKey,
            detail: $detail,
            transactionId: $transactionId,
            ensureNotNegative: true
        );
    }

    /**
     * Refund helper (usually just a credit with type=refund).
     */
    public function refund(
        User $user,
        int $amount,
        ?string $idempotencyKey = null,
        ?array $detail = null,
        ?int $transactionId = null
    ): UserWallet {
        return $this->credit(
            user: $user,
            amount: $amount,
            type: 'refund',
            idempotencyKey: $idempotencyKey,
            detail: $detail,
            transactionId: $transactionId
        );
    }

    /**
     * Core writer: single place to handle locking, idempotency, balance calc.
     */
    private function storeEntry(
        User $user,
        int $amountChange, // signed
        string $type,
        ?string $idempotencyKey,
        ?array $detail,
        ?int $transactionId,
        bool $ensureNotNegative
    ): UserWallet {
        return DB::transaction(function () use (
            $user,
            $amountChange,
            $type,
            $idempotencyKey,
            $detail,
            $transactionId,
            $ensureNotNegative
        ) {
            // 1) Idempotency inside transaction (pre-check)
            if ($idempotencyKey) {
                $existing = UserWallet::where('idempotency_key', $idempotencyKey)->first();
                if ($existing) {
                    return $existing;
                }
            }

            // 2) Lock user row to serialize wallet updates for that user
            //    (No need to add wallet_balance column; we just use the row as a mutex)
            DB::table('users')
                ->where('id', $user->id)
                ->lockForUpdate()
                ->first();

            // 3) Determine current balance from latest ledger row
            $last = UserWallet::where('user_id', $user->id)
                ->latest('id')
                ->first();

            $before = (int) ($last?->balance_after ?? 0);
            $after  = $before + $amountChange;

            if ($ensureNotNegative && $after < 0) {
                throw ValidationException::withMessages([
                    'wallet' => 'Insufficient balance.'
                ]);
            }

            // 4) Insert ledger row
            try {
                return UserWallet::create([
                    'user_id' => $user->id,
                    'transaction_id' => $transactionId,
                    'amount_change' => $amountChange,
                    'balance_before' => $before,
                    'balance_after' => $after,
                    'type' => $type,
                    'idempotency_key' => $idempotencyKey,
                    'detail' => $detail,
                ]);
            } catch (Exception $e) {
                // 5) If two requests raced with same idempotency_key, unique index will throw.
                //    Return the existing entry (idempotent behavior).
                if ($idempotencyKey) {
                    $existing = UserWallet::where('idempotency_key', $idempotencyKey)->first();
                    if ($existing) {
                        return $existing;
                    }
                }
                throw $e;
            }
        });
    }
}

// $wallet = app(\Modules\User\service\WalletService::class)->credit(
//     user: $user,
//     amount: 100,
//     type: 'credit',
//     idempotencyKey: 'providerX:txn_123456',
//     detail: ['provider' => 'providerX', 'raw' => $payload]
// );

// $wallet = app(\Modules\User\service\WalletService::class)->debit(
//     user: $user,
//     amount: 50,
//     type: 'purchase',
//     idempotencyKey: 'order:987',
//     detail: ['order_id' => 987]
// );
// $balance = app(\Modules\User\service\WalletService::class)->balance($user);
