<?php

namespace Modules\AppointmentSetting\app\Models;

use Carbon\Carbon;
use App\Enum\ActiveEnum;
use Modules\User\Entities\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Modules\Service\app\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentSetting\app\Models\AppointmentSegment;
use Modules\AppointmentUser\app\Jobs\GenerateAppointmentCache;
use Modules\AppointmentSetting\app\Models\AppointmentSettingTime;
use Modules\AppointmentSetting\app\Enum\AppintmentSettingDayNumber;
use Modules\AppointmentSetting\app\trait\AppointmentSettingDetailKeyTrait;

class AppointmentSetting extends Model
{
    use HasFactory, SoftDeletes, AppointmentSettingDetailKeyTrait;

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
    public function segments()
    {
        return $this->belongsToMany(AppointmentSegment::class, 'appointment_segment_setting');
    }

    public function appointmentUsers()
    {
        return $this->hasMany(AppointmentUser::class);
    }
    public function ScopeActiveSetting($query)
    {
        return $query->where('active', ActiveEnum::ACTIVE);
    }
    public function timeIsOutOfrange($time)
    {
        // AppintmentSettingDayNumber::
        $userSelectedTime = Carbon::createFromTimestamp($time,  'Asia/Tehran');
        $DayNumber        = AppintmentSettingDayNumber::getConstant(strtolower($userSelectedTime->copy()->format('l')))->value;
        $hasSpecialTime   = $this->times()->where('special_date', $userSelectedTime->copy()->toDateString())->exists();
        if ($hasSpecialTime) {
            $checkForTimeRangeInSpecialDate =    $this->times()
                ->where('special_date', $userSelectedTime->copy()->toDateString())
                ->where('day_number', $DayNumber)
                ->where('start_at', '<=', $userSelectedTime->copy()->format('H:i:s'))
                ->where('end_at', '>=', $userSelectedTime->copy()->format('H:i:s'))->exists();
            if ($checkForTimeRangeInSpecialDate) {
                // selected time is correct and no action needed
                return false;
            }
            return true;
        }

        $normalDayTimeRangeCheck = $this->times()
            ->where('day_number', $DayNumber)
            ->where('start_at', '<=', $userSelectedTime->copy()->format('H:i:s'))
            ->where('end_at', '>=', $userSelectedTime->copy()->format('H:i:s'))->exists();
        if ($normalDayTimeRangeCheck) {
            return false;
        }
        return true;
    }
    public static function SpecialOrGeneralSetting($doctorId, $serviceId = null, $placeId = null)
    {
        $app =  self::Where('user_id', $doctorId)
            ->when($serviceId != null, function ($q) use ($serviceId) {
                return $q->where('service_id', $serviceId);
            })->when($placeId != null, function ($q) use ($placeId) {
                return $q->where('place_id', $placeId);
            })->first();
        if ($app == null) {
            $app =  AppointmentSetting::where('user_id', $doctorId)
                ->whereNull('place_id')
                ->whereNull('service_id')->first();
        }
        return $app;
    }
    public function hasDaySetting($date): bool
    {
        // check if setting for that day exists
        $date = Carbon::parse($date);
        if ($this->times()
            ->where('day_number', AppintmentSettingDayNumber::getConstant(strtolower($date->copy()->format('l'))))
            ->exists()
        ) {
            return true;
        }
        return false;
    }
    public function  runGenerateCacheJob($date,$segment=null)
    {
        Cache::flush();
        $doctorAllSettings = AppointmentSetting::where('user_id', $this->user_id)->get();
        if ($this->interference) {
            foreach ($doctorAllSettings as $setting) {
                if ($setting->hasDaySetting($date)) {
                    GenerateAppointmentCache::dispatch($setting, $date,$segment);
                }
            }
        } else {
            GenerateAppointmentCache::dispatch($this, $date,$segment);
        }
    }
}
