<?php

namespace Modules\Front\enum;

enum FeedbackId: int
{
    case QUESTION_1 = 1;
    case QUESTION_2 = 2;
    case QUESTION_3 = 3;

    public function getQuestion(): string
    {
        return match ($this) {
            self::QUESTION_1 => 'نظر شما در مورد برخورد پزشک چیست ؟',
            self::QUESTION_2 => 'چقدر احتمال دارد این پزشک را به آشنایان خود معرفی کنید ؟',
            self::QUESTION_3 => 'نظر شما در مورد سیستم نوبت دهی چیست ؟',
            default => '' ,
        };
    }

    public function getQuestionChoises(): array
    {
        return match ($this) {
            self::QUESTION_1 =>  [
                'خیلی عالی ',
                ' عالی ',
                'خوب',
                'ضعیف',
            ],
            self::QUESTION_2 =>  [
             'خیلی زیاد ',
                ' زیاد ',
                'متوسط',
                'معرفی نمیکنم',
            ],
            self::QUESTION_3 =>  [
             'خیلی عالی ',
                ' عالی ',
                'خوب',
                'ضعیف',
            ],
            default => [] ,
        };
    }
}
