<?php

namespace Modules\OnlineConsultation\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;

class AppointmentConsultationCaseEvent extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['snapshot' => 'array'];

    public function consultationCase() { return $this->belongsTo(AppointmentConsultationCase::class, 'case_id'); }
    public function actor() { return $this->belongsTo(User::class, 'actor_id'); }
}
