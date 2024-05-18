<?php

namespace Modules\Front\Enum;

enum FeedbackId: int
{
    case QUESTION_1 = 1;
    case QUESTION_2 = 2;

    public function getQuestion(): string
    {
        return match ($this) {
            self::QUESTION_1 => 'از مراحل دریافت نوبت چقدر رضایت دارید؟',
            self::QUESTION_2 => 'چقدر احتمال دارد سیستم نوبت دهی را به ساییر دوستان خود معرفی کنید؟',
            default => '' ,
        };
    }

    public function getQuestionChoises(): array
    {
        return match ($this) {
            self::QUESTION_1 =>  [
                'لورم اپسیوملورم اپسیوملورم اپسیوملورم اپسیوملورم اپسیوملورم لورم اپسیوملورم اپسیوملورم اپسیوملورم اپسیوم',
                ' زیار',
                'متوسط',
                'کم',
            ],
            self::QUESTION_2 =>  [
                'اپسیوملورم اپسیوملورم',
                ' زیار',
                'متوسط',
                'کم',
            ],
            default => [] ,
        };
    }
}
