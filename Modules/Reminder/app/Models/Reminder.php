<?php

namespace Modules\Reminder\app\Models;

use App\Enum\ActiveEnum;
use Modules\User\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Reminder\Enum\ReminderStatusEnum;

class Reminder extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];
    protected $casts = [
        'status'      => ReminderStatusEnum::class,
        'detail'      => 'json',
        'parameters'  => 'json',
        'doctors'     => 'json',
        'active'      => ActiveEnum::class,
    ];
    protected function reminderable()
    {
        return $this->morphTo();
    }

    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
    public function getSendDateString(): string
    {
        $date =  ' در روز نوبت  و '  . $this->send_time . ' ساعت قبل ';
        if (!empty($this->send_day)) {
            $date = $this->send_day . ' روز و ' . $this->send_time . ' ساعت قبل ';
        }
        return $date;
    }
    public function getDoctorsName()
    {
        $docName = 'تمامی پزشکان';
        if (!empty($this->doctors)) {
            $docName = '';
            foreach ($this->doctors as  $key => $doctorId) {
                $user = User::find($doctorId);
                if ($user) {
                    if ($key == 0) {
                        $docName .= $user->fullName;
                    } else {
                        $docName .= ',' . $user->fullName;
                    }
                }
            }
        }
        return $docName;
    }
    public function getDoctorsNameBadge(): string
    {
        $className = '';
        if (empty($this->doctors)) {
            $className =  'badge bg-info rounded-pill';
        }
        return $className;
    }
}
