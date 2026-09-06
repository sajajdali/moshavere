<?php

namespace Modules\Api\Http\Controllers\Voip;

/** Stable numeric results consumed by the VoIP voice-response service. */
final class VoipResponseCode
{
    public const SUCCESS = 0;
    public const INVALID_PHONE = 1001;
    public const APPOINTMENT_NOT_FOUND = 1002;
    public const UNAUTHORIZED = 1401;
    public const VALIDATION_ERROR = 1400;
    public const SERVER_ERROR = 1500;

    private function __construct() {}
}
