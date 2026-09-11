<?php

namespace Modules\OnlineConsultation\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\User\Entities\User;

class AppointmentCallbackRequest extends Model
{
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_ACCEPTED = 'ACCEPTED';
    public const STATUS_FAILED = 'FAILED';

    protected $guarded = ['id'];

    protected $casts = [
        'http_status' => 'integer',
        'duration_ms' => 'integer',
        'request_payload' => 'array',
        'response_payload' => 'array',
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
        'final_call_received_at' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(AppointmentUser::class, 'appointment_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    public function callLog()
    {
        return $this->belongsTo(AppointmentCallLog::class, 'call_id', 'call_id');
    }
}
