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
            self::QUESTION_1 => 'نظر شما راجع به سیستم نوبت دهی چیست ؟',
            self::QUESTION_2 => 'نظر شما راجع برخورد پزشک چیست ؟',
            self::QUESTION_3 => 'نظر شما راجع برخورد ماما با شما چیست ؟',
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
             'خیلی عالی ',
                ' عالی ',
                'خوب',
                'ضعیف',
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
