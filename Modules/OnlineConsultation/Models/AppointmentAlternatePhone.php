<?php

namespace Modules\OnlineConsultation\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\User\Entities\User;

class AppointmentAlternatePhone extends Model
{
    protected $guarded = ['id'];

    public function appointment()
    {
        return $this->belongsTo(AppointmentUser::class);
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
