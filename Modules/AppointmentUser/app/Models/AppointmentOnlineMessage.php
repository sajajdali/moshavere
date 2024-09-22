<?php

namespace Modules\AppointmentUser\app\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;
use Modules\User\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageSeenEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageTypeEnum;
use Modules\AppointmentUser\Database\factories\AppointmentOnlineMessageFactory;

class AppointmentOnlineMessage extends Model
{
    use HasFactory, SoftDeletes;

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

    public function messageFile()
    {
        return $this->hasMany(AppointmentOnlineMessageFile::class, 'fk_id', 'id');
    }
    public static function badgeCount()
    {
        return Self::whereNull('answer_by')->groupBy('appointment_online_id')->count();
    }
    public function unReadedMessageCount()
    {
        return $this->where('appointment_online_id',$this->appointment_online_id)->
        where('type', AppointmentOnlineMessageTypeEnum::QUESTION)
            ->where('seen', AppointmentOnlineMessageSeenEnum::UNSEEN)
            ->whereNull('answer_by')->count();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function online(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AppointmentOnline::class, 'appointment_online_id');
    }

    public function answerBy()
    {
        return $this->belongsTo(User::class, 'answer_by');
    }
    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
    public function countUserMessages()
    {
        return self::where('user_id', $this->user_id)->count();
    }
    public function hasAnswer()
    {
        return $this->online?->messages?->contains(function($message) {
            return $message->answer_by != null;
        });
    }
    public function findAwnswerer():string
    {
        $answer_by =  $this->online?->messages?->reverse()->firstWhere('answer_by', '!=', null);
        return $answer_by->answerBy?->full_name ?? '';
    }
    public static function totalUnreaedMessage():int {
        return Self::whereNull('answer_by')->groupBy('appointment_online_id')->count();
    }
}
