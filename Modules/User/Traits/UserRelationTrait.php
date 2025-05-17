<?php

namespace Modules\User\Traits;

use Modules\Place\app\Models\Place;
use Modules\Front\app\Models\Comment;
use Modules\Absence\app\Models\Absence;
use Modules\Service\app\Models\Service;
use Modules\User\Entities\OperatorTime;
use Modules\Speciality\app\Models\Speciality;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\Discount\app\Models\Discount;

//this Trait is return value as a UerMetaEnum not string
trait UserRelationTrait
{
    public function specialities(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Speciality::class);
    }

    public function service()
    {
        return $this->belongsToMany(Service::class);
    }
    public function absence()
    {
        return $this->hasMany(Absence::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(AppointmentUser::class);
    }

    public function doctorAppointments()
    {
        return $this->hasMany(AppointmentUser::class, 'doctor_id');
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
    public function discounts()
    {
        return $this->belongsToMany(Discount::class, 'discount_user')
            ->withPivot('service_id', 'doctor_id')
            ->withTimestamps();
    }
}
