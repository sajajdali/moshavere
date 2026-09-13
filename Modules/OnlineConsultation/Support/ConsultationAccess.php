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
        'consultation_sms_reminder_rules',
        'appointment_consultant_hangups',
        'appointment_consultant_no_answers',
        'appointment_consultation_cases',
        'appointment_consultation_reports',
        'appointment_consultation_case_events',
        'appointment_callback_requests',
    ];

    /**
     * Only schema shared by every existing route belongs here. Optional feature
     * tables must be checked at their call site so a migration rollout cannot
     * turn the entire module into a 404 response.
     */
    private const RUNTIME_REQUIRED_TABLES = [
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
        'consultation_sms_reminder_rules',
        'appointment_consultant_hangups',
        'appointment_consultant_no_answers',
        'appointment_consultation_cases',
        'appointment_consultation_reports',
        'appointment_consultation_case_events',
    ];

    private const REQUIRED_COLUMNS = [
        'consultation_settings' => ['ignored_short_call_minutes'],
        'consultation_practitioners' => ['voip_host', 'hourly_rate', 'payout_hourly_rate'],
        'appointment_billing_records' => ['payout_hourly_rate_snapshot', 'raw_answered_talk_seconds', 'ignored_talk_seconds', 'connection_overhead_minutes_snapshot', 'billable_talk_seconds', 'practitioner_earned_amount', 'platform_profit_amount'],
        'consultation_sms_deliveries' => ['reminder_rule_id', 'recipient_type', 'rule_title'],
        'appointment_consultation_cases' => ['appointment_note', 'note_author_id', 'note_author_role', 'note_created_at'],
    ];

    public static function enabled(): bool
    {
        return tenancy()->initialized
            && (bool) tenant('online_consultation_enabled')
            && self::schemaReady(self::RUNTIME_REQUIRED_TABLES);
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
