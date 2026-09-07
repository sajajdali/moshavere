<?php

namespace Modules\OnlineConsultation\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\User\Entities\User;

class AppointmentCallLog extends Model
{
    protected $table = 'appointment_call_logs';
    protected $guarded = ['id'];
    protected $casts = [
        'raw_payload' => 'array', 'additional_data' => 'array', 'attempts' => 'array',
        'appointment_start_at' => 'datetime', 'appointment_end_at' => 'datetime',
        'call_entered_at' => 'datetime', 'dial_started_at' => 'datetime',
        'answered_at' => 'datetime', 'ended_at' => 'datetime',
    ];
    protected $hidden = ['raw_payload'];
    public function appointment() { return $this->belongsTo(AppointmentUser::class, 'appointment_id'); }
    public function operator() { return $this->belongsTo(User::class, 'operator_id'); }
}
