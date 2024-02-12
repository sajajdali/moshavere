<?php

namespace Modules\User\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\Place\app\Models\Place;
use Modules\Service\app\Models\Service;
use Modules\Speciality\app\Models\Speciality;
use Modules\User\Enum\UserMetaEnum;

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
}
