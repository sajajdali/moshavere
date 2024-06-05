<?php

namespace Modules\Front\app\Models;

use App\Enum\ActiveEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Faq extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];
    protected $casts = ['active' => ActiveEnum::class];


    public function scopeActive($query)
    {
        return $query->whereActive(true);
    }

    public function scopeInactive($query)
    {
        return $query->whereActive(false);
    }

    public function scopePriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

}
