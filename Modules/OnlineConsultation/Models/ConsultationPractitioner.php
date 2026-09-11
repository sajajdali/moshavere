<?php

namespace Modules\OnlineConsultation\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\User\Entities\User;

class ConsultationPractitioner extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['sip_secret'];

    protected $casts = [
        'active' => 'boolean', 'app_access' => 'boolean',
        'sip_secret' => 'encrypted', 'weekly_schedule' => 'array',
        'hourly_rate' => 'integer', 'payout_hourly_rate' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'practitioner_id');
    }

    public function appointments()
    {
        return $this->hasMany(AppointmentUser::class, 'doctor_id', 'user_id');
    }
}
