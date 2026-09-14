<?php

namespace Modules\PractitionerApi\Services;

use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;

class PractitionerProfileService
{
    public function __construct(private readonly PractitionerSoftphoneService $softphones)
    {
    }

    /** @return array<string, mixed> */
    public function profile(ConsultationPractitioner $practitioner): array
    {
        $practitioner->loadMissing([
            'user.metas',
            'user.specialities:id,title',
            'user.places:id,title',
        ]);
        $user = $practitioner->user;
        $consultationSettings = ConsultationSetting::current();

        return [
            'id' => (int) $practitioner->id,
            'user_id' => (int) $practitioner->user_id,
            'display_name' => (string) $practitioner->display_name,
            'initials' => $this->initials((string) $practitioner->display_name),
            'kind' => (string) $practitioner->kind,
            'specialty' => $practitioner->specialty,
            'specialties' => $user?->specialities
                ?->map(fn ($specialty) => [
                    'id' => (int) $specialty->id,
                    'title' => (string) $specialty->title,
                ])->values()->all() ?? [],
            'activity_centers' => $user?->places
                ?->map(fn ($place) => [
                    'id' => (int) $place->id,
                    'title' => (string) $place->title,
                ])->values()->all() ?? [],
            'avatar_url' => $user?->avatar,
            'license_number' => $user?->dr_licence_number,
            'availability' => (string) $practitioner->availability,
            'booking_enabled' => (bool) $consultationSettings->booking_enabled,
            'extension' => $practitioner->extension,
            'softphone' => $this->softphones->for($practitioner),
            'default_duration_minutes' => (int) ($practitioner->duration_minutes ?: $consultationSettings->duration_minutes),
            'patient_hourly_rate' => (int) ($practitioner->hourly_rate ?? 0),
            'practitioner_hourly_rate' => (int) ($practitioner->payout_hourly_rate ?? 0),
            'server_time' => now()->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    public function sections(ConsultationPractitioner $practitioner): array
    {
        $settings = AppointmentSetting::query()
            ->with(['service:id,title', 'place:id,title', 'times'])
            ->where('user_id', $practitioner->user_id)
            ->orderByRaw('service_id is null and place_id is null desc')
            ->orderBy('id')
            ->get();

        $defaultSetting = $settings->first(fn (AppointmentSetting $setting) => ! $setting->service_id && ! $setting->place_id);
        $sections = $settings
            ->reject(fn (AppointmentSetting $setting) => $defaultSetting?->is($setting))
            ->map(fn (AppointmentSetting $setting) => $this->section($setting, $practitioner))
            ->values()
            ->all();

        return [
            'editable' => false,
            'patient_hourly_rate' => (int) ($practitioner->hourly_rate ?? 0),
            'practitioner_hourly_rate' => (int) ($practitioner->payout_hourly_rate ?? 0),
            'default_hours' => $this->hours($defaultSetting),
            'sections' => $sections,
        ];
    }

    /** @return array<string, mixed> */
    private function section(AppointmentSetting $setting, ConsultationPractitioner $practitioner): array
    {
        $hours = $this->hours($setting);
        $type = $setting->service_id ? 'service' : ($setting->place_id ? 'place' : 'general');
        $entity = $setting->service ?: $setting->place;

        return [
            'key' => $type.':'.($entity?->id ?? $setting->id),
            'type' => $type,
            'name' => (string) ($entity?->title ?? 'برنامه عمومی'),
            'active' => $setting->checkActive(),
            'patient_hourly_rate' => (int) ($practitioner->hourly_rate ?? 0),
            'practitioner_hourly_rate' => (int) ($practitioner->payout_hourly_rate ?? 0),
            'active_days' => collect($hours)->where('closed', false)->count(),
            'hours' => $hours,
        ];
    }

    /** @return list<array{weekday: int, weekday_label: string, from: string|null, to: string|null, closed: bool}> */
    private function hours(?AppointmentSetting $setting): array
    {
        $labels = ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه'];
        $times = $setting?->times?->whereNull('special_date')->groupBy(fn ($time) => (int) $time->day_number->value) ?? collect();

        return collect(range(0, 6))->map(function (int $weekday) use ($labels, $times): array {
            $ranges = $times->get($weekday, collect());
            $first = $ranges->sortBy('start_at')->first();

            return [
                'weekday' => $weekday,
                'weekday_label' => $labels[$weekday],
                'from' => $first ? substr((string) $first->start_at, 0, 5) : null,
                'to' => $first ? substr((string) $first->end_at, 0, 5) : null,
                'closed' => ! $first,
            ];
        })->all();
    }

    private function initials(string $name): string
    {
        $parts = array_values(array_filter(preg_split('/\s+/u', trim($name)) ?: []));
        $parts = array_values(array_filter($parts, fn (string $part) => ! in_array($part, ['دکتر', 'دکتر.', 'مشاور'], true)));

        return collect(array_slice($parts, 0, 2))
            ->map(fn (string $part) => mb_substr($part, 0, 1))
            ->implode('');
    }
}
