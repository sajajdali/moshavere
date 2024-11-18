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
        'detail' => 'json' ,
    ];

    public static function maxPriority()
    {
        return (self::max('priority') ?? 0) + 1;
    }
    public function hasVoice() {
        if(isset($this->detail['voice']) && $this->detail['voice'] != null) {
            return true ;
        }
        return false ;
    }
    public function hasVoiceBadge() {
        if($this->hasVoice()) {
            return 'badge bg-success';
        }
        return 'badge bg-danger';
    }
    public function hasFile() {
        if(isset($this->detail['file']) && $this->detail['file'] != null) {
            return true ;
        }
        return false ;
    }

}
