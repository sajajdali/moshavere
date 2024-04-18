<?php

namespace Modules\Reminder\app\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Service\app\Models\Service;
use Modules\Reminder\Enum\ReminderStatusEnum;

class Reminder extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $table = ['id'];
    protected $casts = [
        'status'      => ReminderStatusEnum::class ,
        'detail'      => 'json' ,
        'parameters'  => 'json' ,
        'doctors'     => 'json' ,
    ] ;
    protected function reminderable()
    {
        return $this->morphTo();
    }
    public function service() {
        $this->morphTo(Service::class);
    }
    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

}
