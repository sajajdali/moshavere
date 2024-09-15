<?php

namespace Modules\AppointmentUser\app\Models;

use App\Models\ShortLink;
use Modules\User\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;

class AppointmentOnline extends Model
{
    use  SoftDeletes;
    const COFRIM_OR_REJECT_STATUS = 'confirm_or_reject_status';
    const BY = 'by';
    const DATE = 'date';

    protected $casts = [
        'status' => AppointmentOnlineStatusEnum::class,
        'details' => 'json',
    ];

    protected $table = 'appointment_online';

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    public function appointmentUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AppointmentUser::class);
    }

    public function messages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AppointmentOnlineMessage::class);
    }

    public function setting()
    {
        return $this->belongsTo(AppointmentSetting::class , 'appointment_setting_id' , 'id');
    }


    public static function generateTrackingCode(): string
    {
        do {
            $uniqueCode = generateUniqueCode(8, true);
        } while (static::where('tracking_code', $uniqueCode)->exists());

        // Insert the unique code into the "transaction" table
        return $uniqueCode;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
    public function shortLink(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(ShortLink::class, 'shortlinkable');
    }
}
