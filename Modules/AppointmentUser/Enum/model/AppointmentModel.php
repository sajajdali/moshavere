<?php

namespace Modules\AppointmentUser\Enum\model;

use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentVia;

class AppointmentModel
{
    public int $timestamp;
    public AppointmentVia $appointmentVia;
    public bool $sendSmsToUser;
    public ?int $serviceId;
    public ?int $placeId;
    public ?int $agentId;
    public ?int $operatorId;
    public ?AppointmentUserKindEnum $kind;
    public ?bool $smsToDoctor;

    /**
     * @param int $timestamp
     * @param AppointmentVia $appointmentVia
     * @param bool $sendSmsToUser
     * @param int|null $serviceId
     * @param int|null $placeId
     */
    public function __construct(int $timestamp, AppointmentVia $appointmentVia = AppointmentVia::SELF, bool $sendSmsToUser = true, ?int $serviceId = null, ?int $placeId = null , ?int $agentId = null,?int $operatorId = null , ?AppointmentUserKindEnum $kind = AppointmentUserKindEnum::IN_PERSION , ?bool $smsToDoctor = false)
    {
        $this->timestamp = $timestamp;
        $this->appointmentVia = $appointmentVia;
        $this->sendSmsToUser = $sendSmsToUser;
        $this->serviceId = $serviceId;
        $this->placeId = $placeId;
        $this->agentId = $agentId;
        $this->operatorId = $operatorId;
        $this->kind = $kind;
        $this->smsToDoctor = $smsToDoctor;
    }


}
