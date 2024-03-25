<?php

namespace Modules\AppointmentUser\Enum;
use App\interface\EnumHasAdminBadgeInterface;
use App\interface\EnumHasApiResultInterface;
use App\interface\EnumHasNameInterface;

enum AppointmentUserKindEnum: int implements EnumHasNameInterface , EnumHasAdminBadgeInterface , EnumHasApiResultInterface
{
    case IN_PERSION = 1;
    case ONLINE = 2;
    case VOIP = 3;

    public function getName(): string
    {
        return match($this) {
            self::IN_PERSION => 'حضوری',
            self::ONLINE => 'آنلاین',
            self::VOIP => 'تلفنی',
        };
    }

    public function getBadge() {
        return match($this) {
            self::IN_PERSION => '<span class="badge bg-success rounded-pill">حضوری</span>',
            self::ONLINE => '<span class="badge bg-danger rounded-pill">آنلاین</span>',
            self::VOIP => '<span class="badge bg-info rounded-pill">تلفنی</span>',
        };
    }
    public function getIcon() {
        return match($this) {
            self::IN_PERSION => '<i class="fa fa-male fa-2x" aria-hidden="true"></i>',
            self::ONLINE => '<i class="fa fa-laptop fa-2x" aria-hidden="true"></i>',
            self::VOIP => '<i class="fa fa-phone fa-2x" aria-hidden="true"></i>',
        };
    }


    public function getAdminBadgeClass(): string
    {
        // TODO: Implement getAdminBadgeClass() method.
    }

    public function getAdminBadge(): string
    {
        return match($this) {
            self::IN_PERSION => '<span class="badge bg-success rounded-pill">حضوری</span>',
            self::ONLINE => '<span class="badge bg-danger rounded-pill">آنلاین</span>',
            self::VOIP => '<span class="badge bg-info rounded-pill">تلفنی</span>',
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
