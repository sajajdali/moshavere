<?php

namespace Modules\Service\app\Models;

use App\Enum\ActiveEnum;
use Modules\User\Entities\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Modules\Reminder\app\Models\Reminder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Service\Enum\ServiceShowTypeEnum;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
}
