<?php

namespace Modules\OnlineConsultation\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\AppointmentUser\app\Models\AppointmentUser;

class AppointmentConsultantNoAnswer extends Model
{
    protected $table = 'appointment_consultant_no_answers';

    protected $guarded = ['id'];

    protected $casts = [
        'no_answer_at' => 'datetime',
        'ring_started_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    protected $hidden = ['raw_payload'];

    public function appointment()
    {
        return $this->belongsTo(AppointmentUser::class, 'appointment_id');
    }

    public function callLog()
    {
        return $this->belongsTo(AppointmentCallLog::class, 'call_id', 'call_id');
    }
}
