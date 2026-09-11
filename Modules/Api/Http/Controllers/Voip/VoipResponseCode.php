<?php

namespace Modules\Api\Http\Controllers\Voip;

/** Stable numeric results consumed by the VoIP voice-response service. */
final class VoipResponseCode
{
    public const SUCCESS = 0;
    public const INVALID_PHONE = 1001;
    public const APPOINTMENT_NOT_FOUND = 1002;
    public const CONSULTATION_COMPLETED = 1003;
    public const UNAUTHORIZED = 1401;
    public const VALIDATION_ERROR = 1400;
    public const SERVER_ERROR = 1500;
    public const INVALID_CALL_LOG = 2001;
    public const INVALID_CONSULTANT_HANGUP = 2002;
    public const INVALID_CONSULTANT_NO_ANSWER = 2003;

    private function __construct() {}
}
