<?php

namespace Modules\Place\app\Models;

use App\Enum\ActiveEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Service\app\Models\Service;
use Modules\User\Entities\User;

class Place extends Model
{

    const DETAIL_KEY_LOCATION = 'location';
    const DETAIL_KEY_NUMBERS = 'numbers';
    const DETAIL_KEY_LOCATION_LAT = 'location_lat';
    const DETAIL_KEY_LOCATION_LNG = 'location_lng';
    const DETAIL_ADDRESS = 'address';
    const DETAIL_TELEGRAM_ADDRESS   = 'telegram_address';
    const DETAIL_INSTAGRAM_ADDRESS  = 'instagram_address';
    const DETAIL_WHATSAPP_ADDRESS   = 'whatsapp_address';
    const DETAIL_PROVINCE   = 'province';
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    protected $casts = [
        'active' => ActiveEnum::class,
        'detail' => 'json',
    ];

    public function user()
    {
        return $this->belongsToMany(User::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'place_user', 'place_id', 'user_id');
    }

    public function checkActive()
    {
        return $this->active == ActiveEnum::ACTIVE->value;
    }
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', ActiveEnum::ACTIVE->value);
    }

    public static function maxPriority(): int
    {
        return self::max('priority') + 1;
    }

    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function apiResult()
    {
        return [
            'id' => $this->id ?? null,
            'title' => $this->title ?? null
        ];
    }
    public function service()
    {
        return $this->belongsToMany(Service::class);
    }
}
