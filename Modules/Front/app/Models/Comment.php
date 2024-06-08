<?php

namespace Modules\Front\app\Models;

use Modules\User\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Front\Enum\CommentStatusEnum;
use Modules\Front\Database\factories\CommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];
    protected $casts = ['status' => CommentStatusEnum::class];

    public function user()
    {
        $this->belongsTo(User::class);
    }
}
