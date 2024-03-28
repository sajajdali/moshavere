<?php

namespace Modules\AppointmentUser\Enum\model;

class UserModelAppointment
{
    public UserModel $userModel;
    public ?UserModel $userSomeoneModel;
    public int $forHimself;

    /**
     * @param int $forHimself
     * @param UserModel $userModel
     * @param UserModel|null $userSomeoneModel
     */
    public function __construct(UserModel $userModel , int $forHimself = 1 , ?UserModel $userSomeoneModel = null)
    {
        $this->userModel = $userModel;
        $this->forHimself = $forHimself;
        $this->userSomeoneModel = $userSomeoneModel;
    }


}
