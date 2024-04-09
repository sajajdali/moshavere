<?php

namespace Modules\AppointmentUser\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;
use Modules\User\Entities\User;

class AppointmentOnline extends Model
{
    use  SoftDeletes;

    protected $casts = [
        'status' => AppointmentOnlineStatusEnum::class,
        'json' => 'json',
    ];

    protected $table = 'appointment_online';

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    public function appointmentUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AppointmentUser::class , 'id');
    }

    public function messages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AppointmentOnlineMessage::class);
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



}
