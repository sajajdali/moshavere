<?php

namespace Modules\AppointmentUser\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\AppointmentUser\Database\factories\AppointmentOnlineFactory;

class AppointmentOnline extends Model
{
    use HasFactory;

    protected $table = 'appointment_online';

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    protected static function newFactory(): AppointmentOnlineFactory
    {
        //return AppointmentOnlineFactory::new();
    }
}
