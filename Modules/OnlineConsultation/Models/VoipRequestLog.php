<?php

namespace Modules\OnlineConsultation\Models;

use Illuminate\Database\Eloquent\Model;

class VoipRequestLog extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['duration_ms' => 'integer', 'response_status' => 'integer', 'error_code' => 'integer'];
}
