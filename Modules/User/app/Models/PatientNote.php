<?php

namespace Modules\User\app\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;

class PatientNote extends Model
{
    protected $guarded = ['id'];

    public function patient()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
