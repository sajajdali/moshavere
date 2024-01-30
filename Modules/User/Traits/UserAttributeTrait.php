<?php

namespace Modules\User\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Modules\User\Enum\UserMetaEnum;

trait UserAttributeTrait
{
    public function firstName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getMeta(UserMetaEnum::FIRST_NAME)?->meta_value,
            set: fn ($value) => $this->metas()->updateOrCreate(
                ['meta_key' => UserMetaEnum::FIRST_NAME,'meta_value' => $value]
            )
        );
    }
    public function lastName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getMeta(UserMetaEnum::LAST_NAME)?->meta_value,
            set: fn ($value) => $this->metas()->updateOrCreate(
                ['meta_key' => UserMetaEnum::LAST_NAME , 'meta_value' => $value]
            )
        );
    }

    public function route(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getMeta(UserMetaEnum::ROUTE)?->meta_value,
            set: fn ($value) => $this->metas()->create([
                'meta_key' => UserMetaEnum::ROUTE,
                'meta_value' => $value
            ])
        );
    }

    public function creator(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getMeta(UserMetaEnum::CREATOR)?->meta_value,
            set: fn ($value) => $this->metas()->create([
                'meta_key' => UserMetaEnum::CREATOR,
                'meta_value' => $value
            ])
        );
    }

    //every could have multi support

    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->first_name . ' ' . $this->last_name,
        );
    }

    public function avatar(): Attribute
    {
        return Attribute::make(
            get: function () {
                $avatar = $this->metas->reverse()->firstWhere('meta_key', UserMetaEnum::AVATAR)?->meta_value;
                if (empty($avatar)) {
                    return url('default/avatar.png');
                }

                return $avatar;
            },
            set: function (?string $value) {
                $this->metas()->create(['meta_key' => UserMetaEnum::AVATAR, 'meta_value' => $value]);
            }
        );
    }
    public function gender(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getMeta(UserMetaEnum::GENDER)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => UserMetaEnum::GENDER, 'meta_value' => $value])
        );
    }

    public function weight(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getMeta(UserMetaEnum::WEIGHT)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => UserMetaEnum::WEIGHT, 'meta_value' => $value])
        );
    }

    public function targetPlan(): Attribute
    {
        $operator = UserMetaEnum::TARGET_PLAN;
        return Attribute::make(
            get: fn() => $this->getMeta($operator)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => $operator, 'meta_value' => $value])
        );
    }

    public function dietType(): Attribute
    {
        $operator = UserMetaEnum::DIET_TYPE;
        return Attribute::make(
            get: fn() => $this->getMeta($operator)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => $operator, 'meta_value' => $value])
        );
    }
    public function dietPlan(): Attribute
    {
        $operator = UserMetaEnum::DIET_PLAN;
        return Attribute::make(
            get: fn() => $this->getMeta($operator)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => $operator, 'meta_value' => $value])
        );
    }
    public function birthday(): Attribute
    {
        $operator = UserMetaEnum::BIRTHDAY;
        return Attribute::make(
            get: fn() => $this->getMeta( $operator)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => $operator, 'meta_value' => $value])
        );
    }

    public function tall(): Attribute
    {
        $operator = UserMetaEnum::TALL;
        return Attribute::make(
            get: fn() => $this->getMeta( $operator)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => $operator, 'meta_value' => $value])
        );
    }

    public function targetWeight(): Attribute
    {
        $operator = UserMetaEnum::TARGET_WEIGHT;
        return Attribute::make(
            get: fn() => $this->getMeta( $operator)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => $operator, 'meta_value' => $value])
        );
    }

    public function bodyFat(): Attribute
    {
        $operator = UserMetaEnum::BODY_FAT;
        return Attribute::make(
            get: fn() => $this->getMeta( $operator)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => $operator, 'meta_value' => $value])
        );
    }

    public function dailyWaterConsumption(): Attribute
    {
        $operator = UserMetaEnum::DAILY_WATER_CONSUMPTION;
        return Attribute::make(
            get: fn() => $this->getMeta( $operator)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => $operator, 'meta_value' => $value])
        );
    }

    public function bodyPhysicalStyle(): Attribute
    {
        $operator = UserMetaEnum::BODY_PHYSICAL_STYLE;
        return Attribute::make(
            get: fn() => $this->getMeta( $operator)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => $operator, 'meta_value' => $value])
        );
    }

    public function bodyStyle(): Attribute
    {
        $operator = UserMetaEnum::BODY_STYLE;
        return Attribute::make(
            get: fn() => $this->getMeta( $operator)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => $operator, 'meta_value' => $value])
        );
    }
    public function typeDailyWork(): Attribute
    {
        $operator = UserMetaEnum::TYPE_DAILY_WORK;
        return Attribute::make(
            get: fn() => $this->getMeta( $operator)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => $operator, 'meta_value' => $value])
        );
    }

    public function foodRestriction(): Attribute
    {
        $operator = UserMetaEnum::FOOD_RESTRICTION;
        return Attribute::make(
            get: fn() => $this->getMeta($operator)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => $operator->value, 'meta_value' => $value])
        );
    }

    public function habits(): Attribute
    {
        $operator = UserMetaEnum::HABITS;
        return Attribute::make(
            get: fn() => $this->getMeta( $operator)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => $operator, 'meta_value' => $value])
        );
    }

    public function wakeUp(): Attribute
    {
        $operator = UserMetaEnum::WAKE_UP;
        return Attribute::make(
            get: fn() => $this->getMeta( $operator)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => $operator, 'meta_value' => $value])
        );
    }


    public function howMuchExperienceSports(): Attribute
    {
        $operator = UserMetaEnum::HOW_MUCH_EXPERIENCE_SPORTS;
        return Attribute::make(
            get: fn() => $this->getMeta( $operator)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => $operator, 'meta_value' => $value])
        );
    }

    public function targetOfExercise(): Attribute
    {
        $operator = UserMetaEnum::TARGET_OF_EXERCISE;
        return Attribute::make(
            get: fn() => $this->getMeta( $operator)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => $operator, 'meta_value' => $value])
        );
    }
    public function activityPerWeek(): Attribute
    {
        $operator = UserMetaEnum::ACTIVITY_PER_WEEK;
        return Attribute::make(
            get: fn() => $this->getMeta( $operator)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => $operator, 'meta_value' => $value])
        );
    }

    public function howManyDaysWeekExercise(): Attribute
    {
        $operator = UserMetaEnum::HOW_MANY_DAYS_WEEK_EXERCISE;
        return Attribute::make(
            get: fn() => $this->getMeta( $operator)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => $operator, 'meta_value' => $value])
        );
    }
    public function weaknessesBody(): Attribute
    {
        $operator = UserMetaEnum::WEAKNESSES_BODY;
        return Attribute::make(
            get: fn() => $this->getMeta( $operator)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => $operator, 'meta_value' => $value])
        );
    }
}
