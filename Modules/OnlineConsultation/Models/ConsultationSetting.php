<?php

namespace Modules\OnlineConsultation\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Setting\Enum\SettingKeyEnum;

class ConsultationSetting extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['voip_secret', 'voip_call_token'];

    protected $casts = [
        'voip_secret' => 'encrypted', 'voip_call_token' => 'encrypted', 'booking_enabled' => 'boolean',
        'app_enabled' => 'boolean', 'test_login_enabled' => 'boolean', 'recording_requested' => 'boolean',
        'consent_required' => 'boolean', 'allow_transfer' => 'boolean',
        'app_landing_content' => 'array',
        'ignored_short_call_minutes' => 'integer',
        'connection_overhead_minutes' => 'integer',
    ];

    public static function current(): self
    {
        $settings = static::firstOrCreate(['id' => 1], [
            'duration_minutes' => 20, 'buffer_minutes' => 5, 'advance_hours' => 2,
            'booking_horizon_days' => 30, 'cancellation_hours' => 12,
            'capacity_per_slot' => 1, 'default_fee' => 0, 'timezone' => 'Asia/Tehran',
            'ring_timeout_seconds' => 30, 'max_attempts' => 2,
            'ignored_short_call_minutes' => 6,
            'connection_overhead_minutes' => 6,
            'connection_method' => 'operator', 'voip_driver' => 'unconfigured',
            'voip_port' => 5061, 'voip_transport' => 'tls',
        ]);
        if (! $settings->voip_host) {
            $legacyAddress = setting(\Modules\Setting\Enum\SettingKeyEnum::VOIP_SERVER_ADDRESS) ?: null;
            $settings->voip_host = filter_var($legacyAddress, FILTER_VALIDATE_URL) ? $legacyAddress : null;
        }
        return $settings;
    }

    public function resolvedVoipUsername(): string
    {
        return trim((string) ($this->voip_username ?: setting(SettingKeyEnum::VOIP_USERNAME)));
    }

    public function resolvedVoipSecret(): string
    {
        return (string) ($this->voip_secret ?: setting(SettingKeyEnum::VOIP_PASSWORD));
    }
}
