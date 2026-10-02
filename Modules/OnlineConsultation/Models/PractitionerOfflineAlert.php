<?php

namespace Modules\OnlineConsultation\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\AppointmentUser\app\Models\AppointmentUser;

class PractitionerOfflineAlert extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'request_payload' => 'array', 'response_payload' => 'array',
        'checked_at' => 'datetime', 'sent_at' => 'datetime',
    ];

    public function appointment() { return $this->belongsTo(AppointmentUser::class); }
    public function practitioner() { return $this->belongsTo(ConsultationPractitioner::class); }
}
