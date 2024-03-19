<?php

namespace Modules\AppointmentSetting\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AppointmentSetting\Database\factories\AppointmentSegmentItemFactory;

class AppointmentSegmentItem extends Model
{
    use HasFactory,softDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    protected static function newFactory(): AppointmentSegmentItemFactory
    {
        //return AppointmentSegmentItemFactory::new();
    }
}
