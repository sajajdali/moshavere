<?php

namespace Modules\Front\app\Models;

use Modules\User\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Front\enum\CommentStatusEnum;
use Modules\Front\Enum\CommentShowHomePage;
use Modules\Front\Database\factories\CommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];
    protected $casts = [
        'status' => CommentStatusEnum::class,
        'show_in_homePage' => CommentShowHomePage::class , 
    ];

    public function user()
    {
       return $this->belongsTo(User::class);
    }
    public function doctor()
    {
       return $this->belongsTo(User::class,'doctor_id');
    }

    public function scopeDoctroComments($query, $doctor_id)
    {
       return  $query->where('doctor_id', $doctor_id)->where('status', CommentStatusEnum::ACCEPTED)->get();
    }
}
