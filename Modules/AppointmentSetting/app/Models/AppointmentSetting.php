<?php

namespace Modules\AppointmentSetting\app\Models;

use App\Enum\ActiveEnum;
use Modules\User\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Service\app\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentSetting\app\trait\AppointmentSettingDetailKeyTrait;

class AppointmentSetting extends Model
{
    use HasFactory, SoftDeletes,AppointmentSettingDetailKeyTrait;

    protected $casts = [
        'active' => ActiveEnum::class,
        'last_day_active' => 'date',
        'interference' => 'boolean',
        'detail' => 'json',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function times()
    {
        return $this->hasMany(AppointmentSettingTime::class);
    }
    public function checkActive()
    {
        return $this->active == ActiveEnum::ACTIVE->value;
    }
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', ActiveEnum::ACTIVE->value);
    }

    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function segments() {
        return $this->belongsToMany(AppointmentSegment::class,'appointment_segment_setting');
    }

    public function appointmentUsers()
    {
        return $this->hasMany(AppointmentUser::class);
    }
    public function ScopeActiveSetting($query) {
        return $this->where('active',ActiveEnum::ACTIVE) ; 
    }
}
