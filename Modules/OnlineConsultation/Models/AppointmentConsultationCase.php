<?php

namespace Modules\OnlineConsultation\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\User\Entities\User;

class AppointmentConsultationCase extends Model
{
    public const STATE_OPEN = 'OPEN';
    public const STATE_COMPLETED = 'COMPLETED';
    public const STATE_PATIENT_NO_SHOW = 'PATIENT_NO_SHOW';

    public function isClosed(): bool
    {
        return in_array($this->state, [self::STATE_COMPLETED, self::STATE_PATIENT_NO_SHOW], true);
    }

    protected $guarded = ['id'];

    protected $casts = [
        'completed_at' => 'datetime',
        'reopened_at' => 'datetime',
        'note_created_at' => 'datetime',
    ];

    public function appointment() { return $this->belongsTo(AppointmentUser::class); }
    public function reports() { return $this->hasMany(AppointmentConsultationReport::class, 'case_id')->latest(); }
    public function events() { return $this->hasMany(AppointmentConsultationCaseEvent::class, 'case_id')->latest(); }
    public function completedBy() { return $this->belongsTo(User::class, 'completed_by'); }
    public function reopenedBy() { return $this->belongsTo(User::class, 'reopened_by'); }
    public function noteAuthor() { return $this->belongsTo(User::class, 'note_author_id'); }
}
