<?php

namespace Modules\Chat\app\Models;

use App\Enum\ActiveEnum;
use Modules\User\Entities\User;
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
        'active' => ActiveEnum::class,
        'detail' => 'json',
    ];

    public static function maxPriority()
    {
        return (self::max('priority') ?? 0) + 1;
    }
    public function hasVoice()
    {
        if (isset($this->detail['voice']) && $this->detail['voice'] != null) {
            return true;
        }
        return false;
    }
    public function hasVoiceBadge()
    {
        if ($this->hasVoice()) {
            return 'badge bg-success';
        }
        return 'badge bg-danger';
    }
    public function hasFile()
    {
        if (isset($this->detail['file']) && $this->detail['file'] != null) {
            return true;
        }
        return false;
    }
    public function scopeDoctorMessage($query, $doctorId)
    {
        return $query->where(function ($query) use ($doctorId) {
            $query->where(function ($query) {
                // Case 1: `doc` contains the string 'null'
                $query->whereJsonContains('detail->doc', 'null');
            })
                ->orWhere(function ($query) {
                    // Case 2: `detail` is null or empty
                    $query->whereNull('detail')
                        ->orWhere('detail', '[]');
                })
                ->orWhere(function ($query) use ($doctorId) {
                    // Case 3: `$doctorId` exists in the `doc` array
                    $query->whereJsonContains('detail->doc', (string) $doctorId);
                });
        });
    }
    public function doctorsLimnits()
    {
        if (! isset($this->detail['doc'])) {
            return 'تمام پزشکان';
        }
        if (isset($this->detail['doc']) && in_array('null', $this->detail['doc'])) {
            return 'تمام پزشکان';
        }
        $doctors_name = [];
        foreach ($this->detail['doc'] as $doctorId) {
            $doctors_name[] = User::find($doctorId)->fullName;
        }
        return implode('<br>', $doctors_name);
    }
}
