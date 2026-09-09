<?php

namespace Modules\OnlineConsultation\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\User\app\Models\UserWallet;
use Modules\User\Entities\User;

class AppointmentBillingAdjustment extends Model
{
    protected $guarded = ['id'];
    public function actor() { return $this->belongsTo(User::class, 'actor_id'); }
    public function walletTransaction() { return $this->belongsTo(UserWallet::class, 'wallet_transaction_id'); }
    public function billingRecord() { return $this->belongsTo(AppointmentBillingRecord::class, 'billing_record_id'); }
}
