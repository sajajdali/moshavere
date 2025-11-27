<?php

namespace Modules\Service\app\Models;

use App\Enum\ActiveEnum;
use Modules\User\Entities\User;
use Modules\Place\app\Models\Place;
use Illuminate\Support\Facades\Cache;
use function PHPUnit\Framework\isNull;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Modules\Reminder\app\Models\Reminder;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Service\Enum\ServiceShowTypeEnum;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

class Service extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    protected $casts = [
        'active' => ActiveEnum::class,
        'show_type' => ServiceShowTypeEnum::class,
        'detail' => 'json',
    ];
    const APP_QUESTION_TITLE  = 'app_question_title';
    const NOT_SHOW_TO_USER    = 'not_show_to_user';

    public function user()
    {
        return $this->belongsToMany(User::class);
    }
    protected function scopeShow()
    {
        return $this->where('show_type', ServiceShowTypeEnum::SHOW);
    }
    public function checkActive()
    {
        return $this->active == ActiveEnum::ACTIVE->value;
    }
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', ActiveEnum::ACTIVE->value);
    }

    // this scope should called where service is load for user
    public function scopeShowToUser(Builder $query)
    {
        return $query->where(function ($q) {
            $q->whereJsonDoesntContain('detail', self::NOT_SHOW_TO_USER)    // Property not set
                ->orWhere('detail->' . self::NOT_SHOW_TO_USER, false);     // Property explicitly false
        });
    }
    public static function maxPriority(): int
    {
        return self::max('priority') + 1;
    }

    public function subSection(): Collection
    {
        return Service::where('parent_id', $this->id)?->get();
    }
    public function reminder(): MorphMany
    {
        return $this->morphMany(Reminder::class, 'reminderable');
    }

    public function apiResult()
    {
        return [
            'id' => $this->id,
            'title' => $this->title
        ];
    }
    public function hasChild()
    {
        return Service::where('parent_id', $this->id)->exists();
    }

    public function isParentCategoryExists($collection)
    {
        return $collection->contains(function ($item) {
            return is_null($item->parent_id);
        });
    }
    public function scopeMostViewedService($query)
    {
        // Define a unique cache key
        $cacheKey = 'most_viewed_service';
        // Attempt to get the data from the cache
        if (app()->environment('local')) {
            return $query->get();
        } else {
            return Cache::remember($cacheKey, 60 * 60, function () use ($query) {
                return $query->get();
            });
        }
    }
    public function getServiceRoute()
    {
        if ($this->user->count() > 1) {
            return  route('front.services', ['service_id' => $this->id, 'service_name' => str_replace(' ', '-', $this->title)]);
        } else {
            $doctor = $this->user->first();
            if ($doctor != null) {
                $placeId = $doctor->activePlaces()->count() == 1 ?  $doctor->activePlaces()->first()->id : null;
                $routeProperty = [
                    'doctor_id' => $doctor->id,
                    'doctor_name' => str_replace(' ', '_', $doctor->fullName),
                    'service_id' => $this->id,
                ];
                if ($placeId != null) {
                    $routeProperty['place_id'] = $placeId;
                    $appointmentSetting = AppointmentSetting::where('place_id', $placeId)
                        ->where('service_id', $this->id)
                        ->where('user_id', $doctor->id)
                        ->first();
                    if ($appointmentSetting === null) {
                        $appointmentSetting = AppointmentSetting::firstWhere('user_id', $doctor->id);
                    }
                    if ($appointmentSetting?->segments()->exists()) {
                        return route('front.doctor.profile', $routeProperty);
                    } else {
                        return route('front.setAppointment.days', $routeProperty);
                    }
                }
                return route('front.doctor.profile', $routeProperty);
            }
            return '';
        }
    }
    public function hasSegment($doctorId, $placeId)
    {
        $appSetting =  AppointmentSetting::SpecialOrGeneralSetting($doctorId, $this->id, $placeId);
        if ($appSetting != null) {
            if ($appSetting->segments->isNotEmpty()) {
                return true;
            }
        }
        return false;
    }
    public function place()
    {
        return $this->belongsToMany(Place::class);
    }
}
