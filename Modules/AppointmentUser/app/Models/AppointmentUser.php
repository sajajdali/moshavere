<?php

namespace Modules\AppointmentUser\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentUser\Database\factories\AppointmentUserFactory;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentUser\Enum\AppointmentUserTypeEnum;
use Modules\Place\app\Models\Place;
use Modules\Service\app\Models\Service;
use Modules\User\Entities\User;

class AppointmentUser extends Model
{
    use HasFactory , SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];
    protected $casts = [
        'type' => AppointmentUserTypeEnum::class,
        'kind' => AppointmentUserKindEnum::class,
        'status' => AppointmentUserStatusEnum::class,
        'date_visit' => 'datetime',
        'visited_at' => 'datetime',
        'details' => 'json',
    ];

    public function setting()
    {
        return $this->belongsTo(AppointmentSetting::class);
    }

    public static function generateTrackingCode(): string
    {
        do {
            $uniqueCode = generateUniqueCode(8 , true);
        } while (static::where('tracking_code', $uniqueCode)->exists());

        // Insert the unique code into the "transaction" table
        return $uniqueCode;
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function place()
    {
        return $this->belongsTo(Place::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class , 'doctor_id' , 'id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class , 'agent_id' , 'id');
    }


}
