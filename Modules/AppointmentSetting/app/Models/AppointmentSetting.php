<?php

namespace Modules\AppointmentSetting\app\Models;

use App\Enum\ActiveEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AppointmentSetting\Database\factories\AppointmentSettingFactory;
use Modules\User\Entities\User;

class AppointmentSetting extends Model
{
    use HasFactory, SoftDeletes;
    protected $casts = [
        'active' => ActiveEnum::class,
        'last_day_active' => 'date',
        'detail' => 'json',
    ];
    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsToMany(User::class);
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
}
