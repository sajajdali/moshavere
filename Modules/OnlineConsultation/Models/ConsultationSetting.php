<?php

namespace Modules\OnlineConsultation\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationSetting extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['voip_secret'];

    protected $casts = [
        'voip_secret' => 'encrypted', 'booking_enabled' => 'boolean',
        'app_enabled' => 'boolean', 'recording_requested' => 'boolean',
        'consent_required' => 'boolean', 'allow_transfer' => 'boolean',
        'ignored_short_call_minutes' => 'integer',
    ];

    public static function current(): self
    {
        $settings = static::firstOrCreate(['id' => 1], [
            'duration_minutes' => 20, 'buffer_minutes' => 5, 'advance_hours' => 2,
            'booking_horizon_days' => 30, 'cancellation_hours' => 12,
            'capacity_per_slot' => 1, 'default_fee' => 0, 'timezone' => 'Asia/Tehran',
            'ring_timeout_seconds' => 30, 'max_attempts' => 2,
            'ignored_short_call_minutes' => 6,
            'connection_method' => 'operator', 'voip_driver' => 'unconfigured',
            'voip_port' => 5061, 'voip_transport' => 'tls',
        ]);
        if (! $settings->voip_host) {
            $legacyAddress = setting(\Modules\Setting\Enum\SettingKeyEnum::VOIP_SERVER_ADDRESS) ?: null;
            $settings->voip_host = filter_var($legacyAddress, FILTER_VALIDATE_URL) ? $legacyAddress : null;
        }
        return $settings;
    }
}
