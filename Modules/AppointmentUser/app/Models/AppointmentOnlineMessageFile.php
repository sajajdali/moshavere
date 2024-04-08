<?php

namespace Modules\AppointmentUser\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppointmentOnlineMessageFile extends Model
{


    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    public function message(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AppointmentOnlineMessage::class , 'fk_id');
    }

}
