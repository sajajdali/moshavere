<?php

namespace Modules\MigrateOldData\App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\AppointmentUser\app\Models\AppointmentUser;

class MigrateAppointmentTransactionsCommand extends Command
{
    protected $signature = 'migrateData:appointment_transactions';

    protected $description = 'Transfer legacy appointment transactions to the new transaction scenario.';

    private const TRANSACTIONABLE_TYPE = 'Modules\\AppointmentUser\\app\\Models\\AppointmentUser';

    public function handle(): int
    {
        $old = DB::connection('old_mysql');
        $new = DB::connection('new_mysql');
        $migrated = 0;
        $skipped = 0;

        $old->table('appointment_users as appointments')
            ->join('transactions as transactions', 'transactions.id', '=', 'appointments.transaction_id')
            ->select([
                'appointments.id as appointment_id',
                'appointments.user_id as appointment_user_id',
                'transactions.id',
                'transactions.user_id',
                'transactions.code',
                'transactions.status',
                'transactions.price',
                'transactions.ip',
                'transactions.type',
                'transactions.doctor_id',
                'transactions.second_type',
                'transactions.created_at',
                'transactions.updated_at',
            ])
            ->orderBy('transactions.id')
            ->chunk(250, function ($transactions) use ($old, $new, &$migrated, &$skipped) {
                $transactionIds = $transactions->pluck('id');
                $metas = $old->table('metas')
                    ->where('has_meta_type', 'Modules\\Finance\\Entities\\Transaction')
                    ->whereIn('has_meta_id', $transactionIds)
                    ->orderBy('id')
                    ->get()
                    ->groupBy('has_meta_id');

                $forcedDeadlines = $old->table('appointment_forced_to_pays')
                    ->whereIn('appointment_user_id', $transactions->pluck('appointment_id'))
                    ->pluck('deadline', 'appointment_user_id');

                foreach ($transactions as $transaction) {
                    $appointment = $new->table('appointment_users')->where('id', $transaction->appointment_id)->first();
                    if (! $appointment) {
                        $skipped++;
                        continue;
                    }

                    $userId = $transaction->user_id ?: $appointment->user_id;
                    if ($userId && ! $new->table('users')->where('id', $userId)->exists()) {
                        $userId = null;
                    }

                    $legacyMetas = [];
                    $legacyMetaRecords = [];
                    foreach ($metas->get($transaction->id, collect()) as $meta) {
                        $metaValue = $this->decodeMetaValue($meta->meta_value);
                        $legacyMetas[$meta->meta_key] = $metaValue;
                        $legacyMetaRecords[] = [
                            'id' => $meta->id,
                            'key' => $meta->meta_key,
                            'value' => $metaValue,
                            'created_at' => $meta->created_at,
                            'updated_at' => $meta->updated_at,
                        ];
                    }

                    $new->table('transactions')->updateOrInsert(
                        ['id' => $transaction->id],
                        [
                            'user_id' => $userId,
                            'discount_id' => null,
                            'transaction_code' => (string) $transaction->code,
                            'transactionable_type' => self::TRANSACTIONABLE_TYPE,
                            'transactionable_id' => $transaction->appointment_id,
                            'status' => $this->mapStatus((int) $transaction->status),
                            'paid_by' => $this->mapPaidBy((int) $transaction->type),
                            'cost' => (int) round($transaction->price),
                            'total_cost' => (int) round($transaction->price),
                            'discount_amount' => 0,
                            'discount_code' => null,
                            'detail' => json_encode([
                                'legacy' => [
                                    'transaction_id' => $transaction->id,
                                    'status' => (int) $transaction->status,
                                    'type' => (int) $transaction->type,
                                    'second_type' => $transaction->second_type === null ? null : (int) $transaction->second_type,
                                    'doctor_id' => $transaction->doctor_id,
                                    'ip' => $transaction->ip,
                                    'metas' => $legacyMetas,
                                    'meta_records' => $legacyMetaRecords,
                                ],
                            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                            'created_at' => $transaction->created_at,
                            'updated_at' => $transaction->updated_at,
                        ]
                    );

                    $details = json_decode($appointment->details ?: '{}', true) ?: [];
                    $details[AppointmentUser::DETAIL_PAYMENT] = [
                        'status' => true,
                        AppointmentUser::DETAIL_PAYMENT_PRICE => [
                            'int' => (int) round($transaction->price),
                            'string' => number_format((int) round($transaction->price)),
                            'currency' => 'ریال',
                        ],
                        AppointmentUser::DETAIL_PAYMENT_PRICE_SOURCE => AppointmentUser::DETAIL_PAYMENT_SOURCE_GENERAL,
                        'legacy_transaction_id' => $transaction->id,
                    ];

                    $appointmentUpdate = [
                        'details' => json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ];
                    if ($forcedDeadlines->has($transaction->appointment_id)) {
                        $appointmentUpdate['deadline_at'] = $forcedDeadlines->get($transaction->appointment_id);
                    }
                    $new->table('appointment_users')->where('id', $transaction->appointment_id)->update($appointmentUpdate);
                    $migrated++;
                }
            });

        $this->info("Appointment transactions transferred: {$migrated}; skipped: {$skipped}.");

        return self::SUCCESS;
    }

    private function mapStatus(int $status): int
    {
        return match ($status) {
            1 => 1,
            0 => 2,
            default => 0,
        };
    }

    private function mapPaidBy(int $type): int
    {
        return match ($type) {
            2, 3 => 2,
            5 => 3,
            default => 1,
        };
    }

    private function decodeMetaValue(?string $value): mixed
    {
        if ($value === null) return null;
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }
}
