<?php

namespace App\Enum;

use App\interface\EnumHasDefaultInterface;

enum RouteEnum: string implements EnumHasDefaultInterface
{
    case APPOINTMENT = '/appointment/{id}';
    case TRANSACTION = '/transaction/show/{id}';
    case ONLINE_MESSAGE = '/support/history/{id}';
    case GET_ONLINE_APPOINTMENT = '/get_appointment/online';
    case GET_IN_PERSON_APPOINTMENT = '/get_appointment/in_person';
    case CHAT = '/chat/{id}';
    case USER_CHATROOM = '/chatroom/{onlineAppId}';


    public static function getDefault(): EnumHasDefaultInterface
    {
        return self::APPOINTMENT;
    }

    public function getLink(string $replacement = '')
    {
        return match ($this) {
            self::APPOINTMENT => str_replace('{id}', $replacement, $this->value),
            self::TRANSACTION => str_replace('{id}', $replacement, $this->value),
            self::ONLINE_MESSAGE => str_replace('{id}', $replacement, $this->value),
            self::CHAT => str_replace('{id}', $replacement, $this->value),
            self::USER_CHATROOM => str_replace('{onlineAppId}', $replacement, $this->value),
            default => $this->value,
        };
    }
}
