<?php

namespace Modules\OnlineConsultation\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationCall extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['started_at' => 'datetime', 'answered_at' => 'datetime', 'ended_at' => 'datetime'];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
}
