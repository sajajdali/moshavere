<?php

namespace Modules\Front\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\Front\Database\factories\FeedBackFactory;

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
        return $this->belongsTo(AppointmentUser::class);
    }
}
