<?php

namespace Modules\Reminder\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\AppointmentUser\app\Models\AppointmentUser;

class AppointmentReminder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];
    protected $casts = ['details' => 'json'];

    public function appointmentUser()
    {
        return $this->belongsTo(AppointmentUser::class);
    }
    public function reminder()
    {
        return $this->belongsTo(Reminder::class);
    }
}
