<?php

namespace Modules\OnlineConsultation\Support;

class ConsultationAccess
{
    public static function enabled(): bool
    {
        return tenancy()->initialized && (bool) tenant('online_consultation_enabled');
    }
}
