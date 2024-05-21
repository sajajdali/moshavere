<?php

namespace Modules\Transaction\app\Models;

use Modules\User\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Transaction\Enum\TransactionPaidEnum;
use Modules\Transaction\Enum\TransactionStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Transaction\Enum\TransactionPaymentForEnum;
use Modules\Transaction\Database\factories\TransactionFactory;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];
    protected $casts = [
        'paid_by'     => TransactionPaidEnum::class ,
        'payment_for' => TransactionPaymentForEnum::class,
        'status'      => TransactionStatusEnum::class ,
        'detail'      => 'json' ,
    ] ;

    protected static function generateUniqueCode()
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';

        // Generate a random code
        for ($i = 0; $i < 8; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $code;
    }
    public static function generateTransactionCode(): string
    {
        do {
            $uniqueCode = static::generateUniqueCode();
        } while (static::where('transaction_code', $uniqueCode)->exists());

        // Insert the unique code into the "transaction" table
        return $uniqueCode;
    }
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return  $this->belongsTo(User::class);
    }

    public function transactionable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}
