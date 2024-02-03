<?php

namespace Modules\Speciality\app\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Speciality\Enum\SpecialityStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Entities\User;

class Speciality extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];
    protected $casts = ['status' => SpecialityStatusEnum::class] ;

    public function scopeFilterStatus($query,SpecialityStatusEnum $status){
        return $query->whereStatus($status);
    }

    public function user() {
        return $this->belongsToMany(User::class,'speciality_user');
    }


    public static function maxOrder(): int
    {
        return self::max('priority') + 1;
    }
}
