<?php

namespace Modules\AppointmentUser\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\AppointmentUser\Database\factories\AppointmentOnlineMessageFactory;

class AppointmentOnlineMessage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];
    
    protected static function newFactory(): AppointmentOnlineMessageFactory
    {
        //return AppointmentOnlineMessageFactory::new();
    }
}
