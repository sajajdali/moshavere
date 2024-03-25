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
                ['meta_key' => UserMetaEnum::FIRST_NAME, 'meta_value' => $value]
            )
        );
    }
   
    public function lastName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getMeta(UserMetaEnum::LAST_NAME)?->meta_value,
            set: fn ($value) => $this->metas()->updateOrCreate(
                ['meta_key' => UserMetaEnum::LAST_NAME, 'meta_value' => $value]
            )
        );
    }

    public function documentNumber(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getMeta(UserMetaEnum::DOCUMENT_NUMBER)?->meta_value,
            set: fn (string $value) => $this->metas()->updateOrCreate(
                ['meta_key' => UserMetaEnum::DOCUMENT_NUMBER],
                ['meta_value' => $value]
            )
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
            get: fn () => $this->getMeta(UserMetaEnum::GENDER)?->meta_value,
            set: fn ($value) => $this->metas()->create(['meta_key' => UserMetaEnum::GENDER, 'meta_value' => $value])
        );
    }
}
