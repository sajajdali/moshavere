<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\System;

use Illuminate\Http\JsonResponse;
use Modules\OnlineConsultation\Support\ConsultationAccess;

class StatusController
{
    /**
     * وضعیت API پزشکان
     *
     * سلامت API و فعال بودن ماژول مشاوره آنلاین در مرکز جاری را برمی‌گرداند.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                'service' => 'practitioner-api',
                'version' => config('practitionerapi.version', '1.0.0'),
                'tenant' => (string) tenant('id'),
                'online_consultation_enabled' => ConsultationAccess::enabled(),
            ],
        ]);
    }
}
