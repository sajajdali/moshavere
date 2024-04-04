<?php

namespace Modules\AppointmentUser\Enum\model;

use Modules\User\Entities\User;

class UserModel
{
    /**
     * @param string|null $firstName
     * @param string|null $lastName
     * @param string|null $mobile
     * @param int|null $gender
     * @param string|null $nationalCode
     * @param int|null $acquainted
     * @param string|null $address
     * @param string|null $city
     */
    public function __construct(
        public ?User $user = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $mobile = null,
        public ?int $gender = null,
        public ?int $age = null,
        public ?string $nationalCode = null,
        public ?int $acquainted = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?BirthdayModel $birthday = null
    ) {
    }


}
