<?php

namespace Modules\OnlineConsultation\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationSmsReminderRule extends Model
{
    public const RECIPIENT_PATIENT = 'patient';
    public const RECIPIENT_PRACTITIONER = 'practitioner';

    protected $guarded = ['id'];

    protected $casts = [
        'active' => 'boolean',
        'minutes_before' => 'integer',
    ];

    public function deliveries()
    {
        return $this->hasMany(ConsultationSmsDelivery::class, 'reminder_rule_id');
    }

    public function recipientLabel(): string
    {
        return $this->recipient_type === self::RECIPIENT_PATIENT ? 'بیمار' : 'پزشک / مشاور';
    }

    public function offsetLabel(): string
    {
        if ($this->minutes_before % 60 === 0) {
            return number_format($this->minutes_before / 60).' ساعت قبل';
        }

        return number_format($this->minutes_before).' دقیقه قبل';
    }
}
