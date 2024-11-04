<?php

namespace Modules\Chat\app\Models;

use App\Enum\ActiveEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MessageTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = [];
    protected $casts = [
        'active' => ActiveEnum::class ,
    ];

    public static function maxPriority()
    {
        return (self::max('priority') ?? 0) + 1;
    }
}
