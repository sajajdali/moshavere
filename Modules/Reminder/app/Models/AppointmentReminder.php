<?php

namespace Modules\Reminder\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Reminder\Database\factories\AppointmentReminderFactory;

class AppointmentReminder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];
    protected $casts = ['details' => 'json'];

}
