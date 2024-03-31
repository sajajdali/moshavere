<?php
namespace Modules\AppointmentUser\Enum\model;

class BirthdayModel
{
    public ?int $day;
    public ?int $month;
    public ?int $year;

    /**
     * @param int|null $day
     * @param int|null $month
     * @param int|null $year
     */
    public function __construct(?int $day = null, ?int $month = null, ?int $year = null)
    {
        $this->day = $day;
        $this->month = $month;
        $this->year = $year;
    }


}
