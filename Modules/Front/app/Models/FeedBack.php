<?php

namespace Modules\Front\app\Models;

use App\Models\ShortLink;
use Illuminate\Database\Eloquent\Model;
use Modules\Front\Database\factories\FeedBackFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\AppointmentUser\app\Models\AppointmentUser;

class FeedBack extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];
    protected $table = 'feedbacks';

    public function appointmentUser()
    {
        return $this->belongsTo(AppointmentUser::class,'appointment_user_id');
    }
    public function shortLink(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(ShortLink::class, 'shortlinkable');
    }
}
