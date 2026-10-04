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

    public function emptyAppointmentDisplayLimit(): ?int
    {
        $limit = data_get($this->detail, self::MAX_EMPTY_APPOINTMENTS_SHOWN_PER_DAY);

        if (! is_numeric($limit) || (int) $limit < 1) {
            return null;
        }

        return (int) $limit;
    }

    /**
     * Number of appointments allowed in a single hour (default 1).
     */
    public function appointmentsPerHour(): int
    {
        $count = data_get($this->detail, self::MULTIPLE_APPOINTMENTS_PER_HOUR . '.count');

        if (! data_get($this->detail, self::MULTIPLE_APPOINTMENTS_PER_HOUR . '.status') || ! is_numeric($count) || (int) $count < 1) {
            return 1;
        }

        return (int) $count;
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
    public function place()
    {
        return $this->belongsTo(\Modules\Place\app\Models\Place::class);
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
    /**
     * The setting that applies to a doctor in a service/place: the doctor's dedicated setting for exactly
     * that service and place, otherwise the doctor's general setting (service_id and place_id null).
     * Never another doctor's, another service's or another place's setting.
     *
     * @param  callable|null  $scope  extra constraint applied to both lookups (e.g. visit type)
     */
    public static function resolveFor($doctorId, $serviceId = null, $placeId = null, bool $activeOnly = false, ?callable $scope = null): ?self
    {
        $base = fn() => self::query()
            ->where('user_id', $doctorId)
            ->when($activeOnly, fn($q) => $q->where('active', ActiveEnum::ACTIVE->value))
            ->when($scope, fn($q) => $scope($q));

        $setting = null;
        if (! empty($serviceId) || ! empty($placeId)) {
            $setting = $base()
                ->when(! empty($serviceId), fn($q) => $q->where('service_id', $serviceId), fn($q) => $q->whereNull('service_id'))
                ->when(! empty($placeId), fn($q) => $q->where('place_id', $placeId), fn($q) => $q->whereNull('place_id'))
                ->first();
        }

        return $setting ?? $base()->whereNull('service_id')->whereNull('place_id')->first();
    }

    public static function SpecialOrGeneralSetting($doctorId, $serviceId = null, $placeId = null)
    {
        return self::resolveFor($doctorId, $serviceId, $placeId);
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
    public function  runGenerateCacheJob($date, $segment = null)
    {
        if (app()->environment('local')) {
            return;
        }
        Cache::flush();
        $doctorAllSettings = AppointmentSetting::where('user_id', $this->user_id)->get();
        if ($this->interference) {
            foreach ($doctorAllSettings as $setting) {
                if ($setting->hasDaySetting($date)) {
                    GenerateAppointmentCache::dispatch($setting, $date, $segment);
                }
            }
        } else {
            GenerateAppointmentCache::dispatch($this, $date, $segment);
        }
    }
    public static function findSettingId($doctorId, $ServiceId, $PlaceId)
    {
        return self::resolveFor($doctorId, $ServiceId, $PlaceId);
    }
    public static function doseSettingHasOperator(self $appointmentSetting): bool
    {
        $d = data_get($appointmentSetting->detail, 'operators', null);
        if (! is_null($d)) {
            if (isset($d['ids']) && isset($d['status']) && $d['status'] == true) {
                return true;
            }
        }
        return false;
    }
    public static function findOperators(self $appointmentSetting)
    {
        $operatorIds = data_get($appointmentSetting->detail, 'operators.ids');
        return User::whereIn('id', $operatorIds)->get();
    }
}
