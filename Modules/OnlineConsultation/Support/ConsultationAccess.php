<?php

namespace Modules\OnlineConsultation\Support;

use Illuminate\Support\Facades\Schema;

class ConsultationAccess
{
    private const REQUIRED_TABLES = [
        'consultation_settings',
        'consultation_practitioners',
        'consultations',
        'consultation_calls',
        'voip_request_logs',
        'appointment_call_logs',
        'appointment_billing_records',
        'appointment_billing_audits',
        'appointment_billing_adjustments',
        'consultation_sms_deliveries',
    ];

    private const REQUIRED_COLUMNS = [
        'consultation_practitioners' => ['voip_host', 'hourly_rate'],
    ];

    public static function enabled(): bool
    {
        return tenancy()->initialized
            && (bool) tenant('online_consultation_enabled')
            && self::schemaReady();
    }

    public static function schemaReady(?array $tables = null): bool
    {
        if (! tenancy()->initialized) {
            return false;
        }

        try {
            foreach ($tables ?? self::REQUIRED_TABLES as $table) {
                if (! Schema::hasTable($table)) {
                    return false;
                }

                if (isset(self::REQUIRED_COLUMNS[$table]) && ! Schema::hasColumns($table, self::REQUIRED_COLUMNS[$table])) {
                    return false;
                }
            }
        } catch (\Throwable) {
            return false;
        }

        return true;
    }

    public static function requiredTables(): array
    {
        return self::REQUIRED_TABLES;
    }
}
