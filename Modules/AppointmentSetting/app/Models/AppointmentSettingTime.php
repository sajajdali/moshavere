<?php

namespace Modules\AppointmentSetting\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentSetting\app\Enum\AppintmentSettingDayNumber;
use Modules\AppointmentSetting\Database\factories\AppointmentSettingTimeFactory;

class AppointmentSettingTime extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    protected $casts = [
        'day_number' => AppintmentSettingDayNumber::class,
    ];

    public function settings()
    {
        return $this->belongsToMany(AppointmentSetting::class);
    }
}
