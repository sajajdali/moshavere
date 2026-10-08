<?php

namespace Modules\AppointmentUser\app\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackAnswer extends Model
{
    protected $guarded = ['id'];

    public function appointmentUser()
    {
        return $this->belongsTo(AppointmentUser::class);
    }

    public function form()
    {
        return $this->belongsTo(FeedbackForm::class, 'feedback_form_id');
    }

    public function question()
    {
        return $this->belongsTo(FeedbackFormQuestion::class, 'feedback_form_question_id');
    }
}
