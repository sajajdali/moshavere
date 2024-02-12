<?php

namespace Modules\Place\app\Models;

use App\Enum\ActiveEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Place\Database\factories\PlaceFactory;
use Modules\User\Entities\User;

class Place extends Model
{

    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    protected $casts = [
        'active' => ActiveEnum::class,
        'detail' => 'json',
    ];

    public function user() {
        return $this->belongsToMany(User::class);
    }

    public function checkActive()
    {
        return $this->active == ActiveEnum::ACTIVE->value ;
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

}
