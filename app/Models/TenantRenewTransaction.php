<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Transaction\Enum\TransactionPaidEnum;
use Modules\Transaction\Enum\TransactionStatusEnum;

class TenantRenewTransaction extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'status' => TransactionStatusEnum::class,
        'paid_by' => TransactionPaidEnum::class,
        'previous_expires_at' => 'datetime',
        'renewed_until' => 'datetime',
        'paid_at' => 'datetime',
        'detail' => 'array',
    ];

    public function getConnectionName()
    {
        return config('tenancy.database.central_connection');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
