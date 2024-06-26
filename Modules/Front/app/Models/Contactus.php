<?php

namespace Modules\Front\app\Models;

use Illuminate\Foundation\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Front\Database\factories\ContactusFactory;

class Contactus extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];
    protected $table = 'contact_us';
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
