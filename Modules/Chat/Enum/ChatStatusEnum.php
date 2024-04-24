<?php

namespace Modules\Chat\Enum;

enum ChatStatusEnum : int
{
    case JUST_CREATED = 0;
    case USER_SEND_QUESTION = 10;
    case USER_SEND_QUESTION_ADMIN_SEEN = 20;
    case ANSWERED = 30;
    case CLOSED = 40;

    public function getName(): string
    {
        return match ($this) {
            self::JUST_CREATED      => 'ایجاد شده',
            self::USER_SEND_QUESTION      => 'ارسال سوال توسط کاربر',
            self::USER_SEND_QUESTION_ADMIN_SEEN      => 'سوال ارسال شده و مدیر مشاهده کرده',
            self::ANSWERED      => 'جواب داده شده',
            self::CLOSED      => 'چت بسته شده'
        };
    }
    public function apiResult(): array
    {
        return [
            'name' => $this->value,
            'body' => $this->getName()
        ];
    }
}
