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
    const MONITORTING_APPOINTMENT = 'monitoring_appointment';
    const IN_PERSON = 'inPerson';
    const OPERATORS = 'operators';
    const IDS = 'ids';
    const ONLINE_CAN_SEND_VOICE = 'onlineCanSendVoice';
    const DONT_SHOW_TIMES =   'dontShowTimes';
    const TEMPORARY_DEACTIVATION_ONLINE =   'TemporaryDeactivationOnline';
    const TEMPORARY_DEACTIVATION_ONLINE_STATUS =   'status';
    const TEMPORARY_DEACTIVATION_ONLINE_MESSAGE =   'message';

    const DONT_SHOW_TIMES_STATUS = 'status';
    const DONT_SHOW_TIMES_MESSAGE = 'message';
    const OPEN_TIME = 'open_time';

    // time to deactive appointment after that
    const MAX_ACTIVE_TIME_ONLINE_APPOINTMENT = 'maxActiveTimeOnlineAppointment';

    // max appointment per day
    const MAX_ACTIVE_APP_FOR_ONLINE_APP  = 'maxActiveAppointmentForOnlineAppointment';

}
