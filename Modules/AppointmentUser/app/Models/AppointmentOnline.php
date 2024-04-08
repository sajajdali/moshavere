<?php

namespace Modules\AppointmentUser\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;

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

    public function AppointmentUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AppointmentUser::class , 'id');
    }

    public static function generateTrackingCode(): string
    {
        do {
            $uniqueCode = generateUniqueCode(8, true);
        } while (static::where('tracking_code', $uniqueCode)->exists());

        // Insert the unique code into the "transaction" table
        return $uniqueCode;
    }

    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }



}
