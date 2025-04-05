<?php

namespace Modules\AppointmentUser\Enum\model;

use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserTypeEnum;
use Modules\AppointmentUser\Enum\AppointmentVia;

class AppointmentModel
{
    // public ?string $description ;

    /**
     * @param int|null $timestamp
     * @param AppointmentVia $appointmentVia
     * @param bool $sendSmsToUser
     * @param int|null $serviceId
     * @param int|null $placeId
     * @param int|null $agentId
     * @param int|null $operatorId
     * @param AppointmentUserKindEnum|null $kind
     * @param bool|null $smsToDoctor
     * @param string|null $description
     * @param AppointmentUserTypeEnum|null $type
     * @param string|null $endTime
     */
    public function __construct(
        public ?int $timestamp = null,
        public AppointmentVia $appointmentVia = AppointmentVia::SELF,
        public bool $sendSmsToUser = true,
        public ?int $serviceId = null,
        public  ?int $placeId = null,
        public ?int $agentId = null,
        public ?int $operatorId = null,
        public ?AppointmentUserKindEnum $kind = AppointmentUserKindEnum::IN_PERSION,
        public ?bool $smsToDoctor = false,
        public ?string $description = '',
        public ?AppointmentUserTypeEnum $type = AppointmentUserTypeEnum::MAIN__APPOINTMENT,
        public ?string $endTime = null,
    ) {
    }
}
