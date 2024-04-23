<?php

namespace Modules\AppointmentSetting\app\trait;

trait AppointmentSettingDetailKeyTrait
{
    const DETAIL_PAYMENT_NOT_PAY_STATUS_DONT_SUBMIT = 'dontSubmit';
    const DETAIL_PAYMENT_NOT_PAY_STATUS_SUBMIT = 'submit';
    const VISIT_TYPE_INPERSON = 'visit_type_inPerson';
    const VISIT_TYPE_VOIP = 'visit_type_voip';
    const VISIT_TYPE_ONLINE = 'visit_type_online';
    const MAX_AVAILABLE_APPOINTMENT_EACH_DAY = 'maxAvailabeAppointment-eachDay';
    const MAX_AVAILABLE_APPOINTMENT_TOTALL = 'maxAvailabeAppointment-totall';
    const MAX_AVAILABLE_APPOINTMENT_FOR_SECRETERY = 'maxAvailabeAppointmentForSecretery';
    const PAYMENT = 'payment';
    const STATUS = 'status';
    const ONLINE = 'online';
    const VOIP = 'voip';
    const PRICE = 'price';
    const NOT_PAYING_STATUS = 'notPayinStatus';
}
