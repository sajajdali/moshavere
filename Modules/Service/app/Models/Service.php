<?php

namespace Modules\Service\app\Models;

use App\Enum\ActiveEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\Entities\User;

class Service extends Model
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

    public function user()
    {
        return $this->belongsToMany(User::class);
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

    public function subSection(): Collection
    {
        return Service::where('parent_id', $this->id)?->get();
    }
}
