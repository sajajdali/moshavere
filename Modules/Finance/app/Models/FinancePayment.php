<?php

namespace Modules\Finance\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\Finance\Enum\FinancePaymentMethod;
use Modules\Finance\Enum\FinancePaymentType;
use Modules\User\Entities\User;

class FinancePayment extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'type' => FinancePaymentType::class,
        'method' => FinancePaymentMethod::class,
        'paid_at' => 'datetime',
        'amount' => 'integer',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function purpose()
    {
        return $this->belongsTo(FinancePaymentPurpose::class, 'purpose_id');
    }

    public function appointment()
    {
        return $this->belongsTo(AppointmentUser::class, 'appointment_user_id')->withTrashed();
    }

    /** the amount with its sign: a refund is negative */
    public function signedAmount(): int
    {
        return $this->amount * $this->type->sign();
    }
}
