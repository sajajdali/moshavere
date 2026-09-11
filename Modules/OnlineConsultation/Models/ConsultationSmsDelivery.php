<?php

namespace Modules\OnlineConsultation\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\User\Entities\User;

class ConsultationSmsDelivery extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['scheduled_at' => 'datetime', 'sent_at' => 'datetime', 'payload' => 'array'];

    public function appointment()
    {
        return $this->belongsTo(AppointmentUser::class);
    }

    public function practitioner()
    {
        return $this->belongsTo(User::class, 'practitioner_id');
    }

    public function reminderRule()
    {
        return $this->belongsTo(ConsultationSmsReminderRule::class, 'reminder_rule_id');
    }
}
