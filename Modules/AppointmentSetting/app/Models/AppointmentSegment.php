<?php

namespace Modules\AppointmentSetting\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppointmentSegment extends Model
{
    use HasFactory , SoftDeletes;

    protected $casts = [
        'active' => 'boolean',
    ];
    
    // multiple_choice : 1 => one choice | 0 => multiple choice ;
    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AppointmentSegmentItem::class);
    }


}
