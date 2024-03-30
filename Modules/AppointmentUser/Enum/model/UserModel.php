<?php

namespace Modules\AppointmentUser\Enum\model;

use Modules\User\Entities\User;

class UserModel
{
    public ?User $user;
    public ?string $firstName;
    public ?string $lastName;
    public ?string $mobile;
    public ?int $gender;
    public ?int $age;
    public ?string $nationalCode;
    public ?int $acquainted;
    public ?string $address;
    public ?string $city;

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
    public function __construct(User $user = null ,?string $firstName = null, ?string $lastName = null, ?string $mobile = null, ?int $gender = null,?int $age = null, ?string $nationalCode = null, ?int $acquainted = null, ?string $address = null, ?string $city = null)
    {
        $this->user = $user;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->mobile = $mobile;
        $this->gender = $gender;
        $this->age = $age;
        $this->nationalCode = $nationalCode;
        $this->acquainted = $acquainted;
        $this->address = $address;
        $this->city = $city;
    }


}
