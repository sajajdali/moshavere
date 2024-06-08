<?php

namespace Modules\User\Traits;

use Modules\Place\app\Models\Place;
use Modules\Front\app\Models\Comment;
use Modules\Service\app\Models\Service;
use Modules\User\Entities\OperatorTime;
use Modules\Speciality\app\Models\Speciality;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

//this Trait is return value as a UerMetaEnum not string
trait UserRelationTrait
{
    public function specialities(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Speciality::class);
    }

    public function appointmentSettings(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(AppointmentSetting::class);
    }

    public function places(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Place::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class);
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function operatorTimes()
    {
        return $this->hasMany(OperatorTime::class, 'oprator_id');
    }
}
