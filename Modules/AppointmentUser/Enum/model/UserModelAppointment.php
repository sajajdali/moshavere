<?php

namespace Modules\AppointmentUser\Enum\model;

class UserModelAppointment
{

    /**
     * @param int $forHimself
     * @param UserModel $userModel
     * @param UserModel|null $userSomeoneModel
     * @param bool $needToUpdate
     */
    public function __construct(
        public UserModel $userModel,
        public int $forHimself = 1,
        public ?UserModel $userSomeoneModel = null,
        public bool $needToUpdate = false
    ) {
    }
}
