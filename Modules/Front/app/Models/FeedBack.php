<?php

namespace Modules\Front\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Front\Database\factories\FeedBackFactory;

class FeedBack extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];
    
    protected static function newFactory(): FeedBackFactory
    {
        //return FeedBackFactory::new();
    }
}
