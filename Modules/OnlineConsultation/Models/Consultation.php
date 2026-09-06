<?php

namespace Modules\OnlineConsultation\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['scheduled_at' => 'datetime', 'ended_at' => 'datetime'];

    public function practitioner()
    {
        return $this->belongsTo(ConsultationPractitioner::class, 'practitioner_id');
    }

    public function calls()
    {
        return $this->hasMany(ConsultationCall::class);
    }
}
