<?php

namespace Modules\AppointmentUser\app\Models;

use Modules\User\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageSeenEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageTypeEnum;
use Modules\AppointmentUser\Database\factories\AppointmentOnlineMessageFactory;

class AppointmentOnlineMessage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    // TODO::'seen' => has been change from boolian to  AppointmentOnlineMessageSeenEnum::class ;
    protected $casts = [
        'type' => AppointmentOnlineMessageTypeEnum::class,
        'seen' => AppointmentOnlineMessageSeenEnum::class,
        'details' => 'json',
    ];

    public function files(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AppointmentOnlineMessageFile::class ,'fk_id' );
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function online(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AppointmentOnline::class , 'appointment_online_id');
    }

    public function answerBy()
    {
        return $this->belongsTo(User::class , 'answer_by');
    }
    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

}
