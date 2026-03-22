<?php

namespace Modules\User\Enum;

enum UserWalletTypeEnum: string
{
    case    CREDIT = 'credit';
    case    PURCHASE = 'purchase';
    case    REFUND = 'refund';
    case    ADMIN_ADJUSTMENT = 'admin_adjustment';
}
