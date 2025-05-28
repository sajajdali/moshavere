<?php

namespace Modules\User\Traits;

use Modules\User\Enum\UserMetaEnum;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait UserAttributeTrait
{
    public function firstName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getMeta(UserMetaEnum::FIRST_NAME)?->meta_value,
            set: fn($value) => $this->metas()->updateOrCreate(
                ['meta_key' => UserMetaEnum::FIRST_NAME],
                ['meta_value' => $value]
            )
        );
    }

    public function lastName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getMeta(UserMetaEnum::LAST_NAME)?->meta_value,
            set: fn($value) => $this->metas()->updateOrCreate(
                ['meta_key' => UserMetaEnum::LAST_NAME],
                ['meta_value' => $value]
            )
        );
    }

    public function city(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getMeta(UserMetaEnum::CITY)?->meta_value,
            set: fn($value) => $this->metas()->updateOrCreate(
                ['meta_key' => UserMetaEnum::CITY],
                ['meta_value' => $value]
            )
        );
    }

    public function nationalCode(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getMeta(UserMetaEnum::NATIONAL_CODE)?->meta_value,
            set: fn($value) => $this->metas()->updateOrCreate(
                ['meta_key' => UserMetaEnum::NATIONAL_CODE],
                ['meta_value' => $value]
            )
        );
    }

    public function documentNumber(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getMeta(UserMetaEnum::DOCUMENT_NUMBER)?->meta_value,
            set: fn(string $value) => $this->metas()->updateOrCreate(
                ['meta_key' => UserMetaEnum::DOCUMENT_NUMBER],
                ['meta_value' => $value]
            )
        );
    }


    public function creator(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getMeta(UserMetaEnum::CREATOR)?->meta_value,
            set: fn($value) => $this->metas()->create([
                'meta_key' => UserMetaEnum::CREATOR,
                'meta_value' => $value
            ])
        );
    }

    //every could have multi support

    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->first_name . ' ' . $this->last_name,
        );
    }

    public function avatar(): Attribute
    {
        return Attribute::make(
            get: function () {
                $avatar = $this->metas
                    ->where('meta_key', UserMetaEnum::AVATAR)
                    ->sortByDesc('created_at')
                    ->first()?->meta_value;

                // If no avatar found, return the default avatar URL
                return $avatar ?: url('default/avatar.png');
            },
            set: function (?string $value) {
                $this->metas()->updateOrCreate(['meta_key' => UserMetaEnum::AVATAR], ['meta_value' => $value]);
            }
        );
    }
    public function gender(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getMeta(UserMetaEnum::GENDER)?->meta_value,
            set: fn($value) => $this->metas()->updateOrCreate(['meta_key' => UserMetaEnum::GENDER], ['meta_value' => $value])
        );
    }

    // DOCTOR ATTRIBUTE
    public function specialityType(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getMeta(UserMetaEnum::SPECIALITY_TYPE)?->meta_value,
            set: fn($value) => $this->metas()->updateOrCreate(['meta_key' => UserMetaEnum::SPECIALITY_TYPE], ['meta_value' => $value])
        );
    }
    public function drBanner(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getMeta(UserMetaEnum::DR_BANNER)?->meta_value,
            set: fn($value) => $this->metas()->updateOrCreate(['meta_key' => UserMetaEnum::DR_BANNER], ['meta_value' => $value])
        );
    }

    public function drBiography(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getMeta(UserMetaEnum::DOC_BIOGRAPHY)?->meta_value,
            set: fn($value) => $this->metas()->updateOrCreate(['meta_key' => UserMetaEnum::DOC_BIOGRAPHY], ['meta_value' => $value])
        );
    }
    public function drLicenceNumber(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getMeta(UserMetaEnum::LICENCE_NUMBER)?->meta_value,
            set: fn($value) => $this->metas()->updateOrCreate(['meta_key' => UserMetaEnum::LICENCE_NUMBER], ['meta_value' => $value])
        );
    }
    public function drAddress(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getMeta(UserMetaEnum::DOC_ADDRESS)?->meta_value,
            set: fn($value) => $this->metas()->updateOrCreate(['meta_key' => UserMetaEnum::DOC_ADDRESS], ['meta_value' => $value])
        );
    }
    public function drOrder(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getMeta(UserMetaEnum::DOCTOR_ORDER)?->meta_value,
            set: function ($value) {
                // Update or create the meta value
                $this->metas()->updateOrCreate(
                    ['meta_key' => UserMetaEnum::DOCTOR_ORDER],
                    ['meta_value' => $value]
                );
                if (! app()->environment('local')) {
                // Remove specific caches
                Cache::forget('emergency_doctors');
                Cache::forget('Introduction_doctors');
                }
            }
        );
    }
    public function activeAppointment(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getMeta(UserMetaEnum::ACTIVE_APPOINTMENT)?->meta_value,
            set: function ($value) {
                // Update or create the meta value
                $this->metas()->updateOrCreate(
                    ['meta_key' => UserMetaEnum::ACTIVE_APPOINTMENT],
                    ['meta_value' => $value]
                );
                if (! app()->environment('local')) {
                    // Remove specific caches
                    Cache::forget('emergency_doctors');
                    Cache::forget('Introduction_doctors');
                }
            }
        );
    }
    public function banUser(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getMeta(UserMetaEnum::BAN_USER)?->meta_value,
            set: function ($value) {
                // Update or create the meta value
                $this->metas()->updateOrCreate(
                    ['meta_key' => UserMetaEnum::BAN_USER],
                    ['meta_value' => $value]
                );
                if (! app()->environment('local')) {
                    // Remove specific caches
                    Cache::forget('emergency_doctors');
                    Cache::forget('Introduction_doctors');
                }
            }
        );
    }

    public function birthday(): Attribute
    {
        $operator = UserMetaEnum::BIRTHDAY;
        return Attribute::make(
            get: fn() => $this->getMeta($operator)?->meta_value,
            set: fn($value) => $this->metas()->updateOrCreate(['meta_key' => $operator], ['meta_value' => $value])
        );
    }
    public function drGallery(): Attribute
    {
        $operator = UserMetaEnum::DR_GALLERY;
        return Attribute::make(
            get: fn() => $this->getMeta($operator)?->meta_value,
            set: fn($value) => $this->metas()->create(['meta_key' => $operator, 'meta_value' => $value])
        );
    }
    public function drEmergencyvisitStatus(): Attribute
    {
        $operator = UserMetaEnum::DR_ENEMRGENCY_STATUS;
        return Attribute::make(
            get: fn() => $this->getMeta($operator)?->meta_value,
            set: function ($value) use ($operator) {
                // Update or create the meta value
                $this->metas()->updateOrCreate(
                    ['meta_key' => $operator],
                    ['meta_value' => $value]
                );
                if (! app()->environment('local')) {
                    // Remove specific caches
                    Cache::forget('emergency_doctors');
                    Cache::forget('Introduction_doctors');
                }
            }
        );
    }
    public function drEmergencyvisitOrder(): Attribute
    {
        $operator = UserMetaEnum::DR_ENEMRGENCY_ORDER;
        return Attribute::make(
            get: fn() => $this->getMeta($operator)?->meta_value,
            set: function ($value) use ($operator) {
                // Update or create the meta value
                $this->metas()->updateOrCreate(
                    ['meta_key' => $operator],
                    ['meta_value' => $value]
                );
                // Remove specific caches
                if (! app()->environment('local')) {
                    Cache::forget('emergency_doctors');
                    Cache::forget('Introduction_doctors');
                }
            }
        );
    }
    public function drInfoStatus(): Attribute
    {
        $operator = UserMetaEnum::DR_INFO_STATUS;
        return Attribute::make(
            get: fn() => $this->getMeta($operator)?->meta_value,
            set: function ($value) use ($operator) {
                // Update or create the meta value
                $this->metas()->updateOrCreate(
                    ['meta_key' => $operator],
                    ['meta_value' => $value]
                );
                // Remove specific caches
                if (! app()->environment('local')) {
                    Cache::forget('emergency_doctors');
                    Cache::forget('Introduction_doctors');
                }
            }
        );
    }
    public function drInfoOrder(): Attribute
    {
        $operator = UserMetaEnum::DR_INFO_ORDER;
        return Attribute::make(
            get: fn() => $this->getMeta($operator)?->meta_value,
            set: function ($value) use ($operator) {
                // Update or create the meta value
                $this->metas()->updateOrCreate(
                    ['meta_key' => $operator],
                    ['meta_value' => $value]
                );
                if (! app()->environment('local')) {
                    // Remove specific caches
                    Cache::forget('emergency_doctors');
                    Cache::forget('Introduction_doctors');
                }
            }
        );
    }
    public function drWaitingTime(): Attribute
    {
        $operator = UserMetaEnum::DR_WAITING_TIME;
        return Attribute::make(
            get: fn() => $this->getMeta($operator)?->meta_value,
            set: function ($value) use ($operator) {
                // Update or create the meta value
                $this->metas()->updateOrCreate(
                    ['meta_key' => $operator],
                    ['meta_value' => $value]
                );
            }
        );
    }
    public function drDisplayMobile(): Attribute
    {
        $operator = UserMetaEnum::DR_WEBSITE_DISPLAY_MOBILE;
        return Attribute::make(
            get: fn() => $this->getMeta($operator)?->meta_value,
            set: function ($value) use ($operator) {
                // Update or create the meta value
                $this->metas()->updateOrCreate(
                    ['meta_key' => $operator],
                    ['meta_value' => $value]
                );
            }
        );
    }
    public function drDisplayNavigation(): Attribute
    {
        $operator = UserMetaEnum::DR_WEBSITE_DISPLAY_NAVIGATION;
        return Attribute::make(
            get: fn() => $this->getMeta($operator)?->meta_value,
            set: function ($value) use ($operator) {
                // Update or create the meta value
                $this->metas()->updateOrCreate(
                    ['meta_key' => $operator],
                    ['meta_value' => $value]
                );
            }
        );
    }
    public function drDisplayAddress(): Attribute
    {
        $operator = UserMetaEnum::DR_WEBSITE_DISPLAY_ADDRESS;
        return Attribute::make(
            get: fn() => $this->getMeta($operator)?->meta_value,
            set: function ($value) use ($operator) {
                // Update or create the meta value
                $this->metas()->updateOrCreate(
                    ['meta_key' => $operator],
                    ['meta_value' => $value]
                );
            }
        );
    }
    public function drDisplayExperince(): Attribute
    {
        $operator = UserMetaEnum::DR_WEBSITE_DISPLAY_EXPERINCE;
        return Attribute::make(
            get: fn() => $this->getMeta($operator)?->meta_value,
            set: function ($value) use ($operator) {
                // Update or create the meta value
                $this->metas()->updateOrCreate(
                    ['meta_key' => $operator],
                    ['meta_value' => $value]
                );
            }
        );
    }
    public function drDisplayDiscription(): Attribute
    {
        $operator = UserMetaEnum::DR_WEBSITE_DISPLAY_DESCRIPTION;
        return Attribute::make(
            get: fn() => $this->getMeta($operator)?->meta_value,
            set: function ($value) use ($operator) {
                // Update or create the meta value
                $this->metas()->updateOrCreate(
                    ['meta_key' => $operator],
                    ['meta_value' => $value]
                );
            }
        );
    }
    public function drRegistrationDescription(): Attribute
    {
        $operator = UserMetaEnum::DR_REGISTRATION_DESCRIPTION;
        return Attribute::make(
            get: fn() => $this->getMeta($operator)?->meta_value,
            set: function ($value) use ($operator) {
                // Update or create the meta value
                $this->metas()->updateOrCreate(
                    ['meta_key' => $operator],
                    ['meta_value' => $value]
                );
            }
        );
    }
    public function favoriteDr(): Attribute
    {
        return Attribute::make(
            get: fn() => json_decode($this->getMeta(UserMetaEnum::FAVORITE_DOCTOR)?->meta_value),
            set: function ($value) {
                // Update the meta value
                $this->metas()->updateOrCreate(
                    ['user_id' => $this->id, 'meta_key' => UserMetaEnum::FAVORITE_DOCTOR],
                    ['meta_value' => json_encode($value)]
                );
            }
        );
    }
    public function drRegisterFrom(): Attribute
    {
        $operator = UserMetaEnum::DR_REGISTRATION_FROM;
        return Attribute::make(
            get: fn() => $this->getMeta($operator)?->meta_value,
            set: fn($value) => $this->metas()->create(['meta_key' => $operator, 'meta_value' => $value])
        );
    }
    public function drRate(): Attribute
    {
        $operator = UserMetaEnum::DR_RATE;
        return Attribute::make(
            get: fn() => $this->getMeta($operator)?->meta_value,
            set: function ($value) use ($operator) {
                // Update or create the meta value
                $this->metas()->updateOrCreate(
                    ['meta_key' => $operator],
                    ['meta_value' => $value]
                );
            }
        );
    }
    public function drStoreAppSms(): Attribute
    {
        $operator = UserMetaEnum::DR_STORE_APP_SMS;
        return Attribute::make(
            get: fn() => $this->getMeta($operator)?->meta_value,
            set: function ($value) use ($operator) {
                // Update or create the meta value
                $this->metas()->updateOrCreate(
                    ['meta_key' => $operator],
                    ['meta_value' => $value]
                );
            }
        );
    }
}
