<?php

namespace Modules\PractitionerApi\Services;

use InvalidArgumentException;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;
use Modules\Setting\Entities\Setting;
use Modules\Setting\Enum\SettingKeyEnum;

class PractitionerSettingsService
{
    /** @return array<string, mixed> */
    public function landing(): array
    {
        $stored = ConsultationSetting::current()->app_landing_content ?? [];
        $content = array_replace(config('practitionerapi.landing', []), $stored);
        $content['app_name'] = $content['app_name'] ?? Setting::v(SettingKeyEnum::SITE_TITLE) ?: config('app.name');

        return $content;
    }

    /** @return list<array{key: string, label: string, value: bool}> */
    public function all(ConsultationPractitioner $practitioner): array
    {
        $stored = $practitioner->app_settings ?? [];

        return collect(config('practitionerapi.settings', []))
            ->map(fn (array $definition, string $key) => [
                'key' => $key,
                'label' => (string) $definition['label'],
                'value' => (bool) ($stored[$key] ?? $definition['default']),
            ])->values()->all();
    }

    /** @return array{key: string, label: string, value: bool} */
    public function update(ConsultationPractitioner $practitioner, string $key, bool $value): array
    {
        $definitions = config('practitionerapi.settings', []);
        if (! array_key_exists($key, $definitions)) {
            throw new InvalidArgumentException('Unknown practitioner setting.');
        }

        $settings = $practitioner->app_settings ?? [];
        $settings[$key] = $value;
        $practitioner->update(['app_settings' => $settings]);

        return ['key' => $key, 'label' => (string) $definitions[$key]['label'], 'value' => $value];
    }
}
