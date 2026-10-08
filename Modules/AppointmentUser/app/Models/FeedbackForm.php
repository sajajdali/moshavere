<?php

namespace Modules\AppointmentUser\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Place\app\Models\Place;
use Modules\Service\app\Models\Service;
use Modules\User\Entities\User;

class FeedbackForm extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = ['active' => 'boolean'];

    public function questions()
    {
        return $this->hasMany(FeedbackFormQuestion::class)->orderBy('sort');
    }

    public function answers()
    {
        return $this->hasMany(FeedbackAnswer::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function place()
    {
        return $this->belongsTo(Place::class);
    }
}
