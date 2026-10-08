<?php

namespace Modules\AppointmentUser\app\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackFormQuestion extends Model
{
    const TYPE_TEXT = 'text';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_SELECT = 'select';
    const TYPE_RADIO = 'radio';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_RATING = 'rating';

    protected $guarded = ['id'];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
    ];

    public static function types(): array
    {
        return [
            self::TYPE_TEXT => 'متن کوتاه',
            self::TYPE_TEXTAREA => 'متن بلند',
            self::TYPE_SELECT => 'لیست کشویی (انتخاب یک گزینه)',
            self::TYPE_RADIO => 'گزینه‌های تک انتخابی',
            self::TYPE_CHECKBOX => 'چند انتخابی',
            self::TYPE_RATING => 'امتیاز ۱ تا ۵',
        ];
    }

    public static function typesWithOptions(): array
    {
        return [self::TYPE_SELECT, self::TYPE_RADIO, self::TYPE_CHECKBOX];
    }

    public function form()
    {
        return $this->belongsTo(FeedbackForm::class, 'feedback_form_id');
    }
}
