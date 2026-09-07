<?php

namespace Modules\OnlineConsultation\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;

class AppointmentBillingAudit extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['snapshot' => 'array'];
    public function actor() { return $this->belongsTo(User::class, 'actor_id'); }
    public function billingRecord() { return $this->belongsTo(AppointmentBillingRecord::class, 'billing_record_id'); }
}
