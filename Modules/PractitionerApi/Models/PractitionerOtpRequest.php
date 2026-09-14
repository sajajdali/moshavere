<?php

namespace Modules\PractitionerApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class PractitionerOtpRequest extends Model
{
    use Notifiable;

    protected $guarded = ['id'];

    protected $hidden = ['code_hash'];

    protected $casts = [
        'attempts' => 'integer',
        'request_count' => 'integer',
        'expires_at' => 'datetime',
        'next_request_at' => 'datetime',
        'locked_until' => 'datetime',
        'consumed_at' => 'datetime',
    ];
}
