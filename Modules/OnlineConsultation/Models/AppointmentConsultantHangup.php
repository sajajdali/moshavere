<?php

namespace Modules\OnlineConsultation\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\AppointmentUser\app\Models\AppointmentUser;

class AppointmentConsultantHangup extends Model
{
    public const VIA_PHONE = 'PHONE';
    public const VIA_SOFTPHONE = 'SOFTPHONE';

    protected $table = 'appointment_consultant_hangups';

    protected $guarded = ['id'];

    protected $casts = [
        'hung_up_at' => 'datetime',
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
