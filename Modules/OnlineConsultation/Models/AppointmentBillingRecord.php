<?php

namespace Modules\OnlineConsultation\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\User\App\Models\UserWallet;
use Modules\User\Entities\User;

class AppointmentBillingRecord extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['approved_at' => 'datetime'];

    public function appointment() { return $this->belongsTo(AppointmentUser::class); }
    public function patient() { return $this->belongsTo(User::class, 'patient_id'); }
    public function practitioner() { return $this->belongsTo(User::class, 'practitioner_id'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function walletTransaction() { return $this->belongsTo(UserWallet::class, 'wallet_transaction_id'); }
    public function audits() { return $this->hasMany(AppointmentBillingAudit::class, 'billing_record_id')->latest(); }
    public function adjustments() { return $this->hasMany(AppointmentBillingAdjustment::class, 'billing_record_id')->oldest(); }
}
