<?php

namespace Modules\User\Livewire\Admin\User;

use App\Enum\ActiveEnum;
use App\Models\ShortLink;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\Place\app\Models\Place;
use Modules\User\Entities\User;

/**
 * مدال «لینک خدمت مشخص» در لیست کاربران:
 * برای هر بخش و مطب پزشک یک لینک کوتاه معنی دار (/s/dr-...) ساخته میشود
 * که پروفایل پزشک را با همان بخش و مطب باز می کند.
 */
class DoctorServiceLinks extends Component
{
    public ?int $doctorId = null;

    public string $doctorName = '';

    public array $rows = [];

    // کد قابل ویرایش هر ردیف، با کلید «s{serviceId}_p{placeId}»
    public array $codes = [];

    #[On('open-doctor-service-links')]
    public function open(int $doctorId): void
    {
        $doctor = User::findOrFail($doctorId);
        $this->authorize('update', $doctor);
        abort_unless($doctor->IsDoctor(), 404);

        $this->doctorId = $doctor->id;
        $this->doctorName = (string) $doctor->full_name;
        $this->codes = [];
        $this->resetErrorBag();
        $this->loadRows($doctor);

        $this->dispatch('show-doctor-service-links-modal');
    }

    public function createLink(string $key): void
    {
        $doctor = $this->doctor();
        $row = collect($this->rows)->firstWhere('key', $key);
        abort_unless($row, 404);

        if (ShortLink::forDoctorService($doctor->id, $row['service_id'], $row['place_id'])->exists()) {
            $this->loadRows($doctor);

            return;
        }

        $code = strtolower(trim((string) ($this->codes[$key] ?? '')));
        if (! preg_match(ShortLink::CODE_PATTERN, $code)) {
            $this->addError('codes.'.$key, 'فقط حروف کوچک انگلیسی، عدد و خط تیره (حداکثر ۶۰ حرف).');

            return;
        }
        if (ShortLink::where('link_code', $code)->exists()) {
            $this->addError('codes.'.$key, 'این آدرس قبلاً استفاده شده است؛ پیشنهاد: '.ShortLink::uniqueCode($code));

            return;
        }

        ShortLink::create([
            'shortlinkable_type' => User::class,
            'shortlinkable_id' => $doctor->id,
            'link_code' => $code,
            // مقصد واقعی هنگام باز شدن از details ساخته میشود (ShortLink::targetUrl)
            'link_url' => '/doctor/'.$doctor->id,
            'details' => [
                'type' => ShortLink::TYPE_DOCTOR_SERVICE,
                'service_id' => $row['service_id'],
                'place_id' => $row['place_id'],
            ],
        ]);

        $this->resetErrorBag('codes.'.$key);
        $this->loadRows($doctor);
    }

    public function deleteLink(int $linkId): void
    {
        $doctor = $this->doctor();

        ShortLink::whereKey($linkId)
            ->where('shortlinkable_type', User::class)
            ->where('shortlinkable_id', $doctor->id)
            ->where('details->type', ShortLink::TYPE_DOCTOR_SERVICE)
            ->delete();

        $this->loadRows($doctor);
    }

    private function doctor(): User
    {
        $doctor = User::findOrFail($this->doctorId);
        $this->authorize('update', $doctor);

        return $doctor;
    }

    /**
     * ترکیب بخش ها و مطب های پزشک؛ همان قواعد انتخاب مطب و بخش در رزرو آنلاین
     * (TenantAppController::availablePlaces / availableServices).
     */
    private function loadRows(User $doctor): void
    {
        $services = $doctor->services()
            ->where('services.active', ActiveEnum::ACTIVE->value)
            ->orderBy('priority')
            ->get();
        $places = $doctor->places()->where('places.active', ActiveEnum::ACTIVE->value)->orderBy('priority')->get();
        $links = ShortLink::where('shortlinkable_type', User::class)
            ->where('shortlinkable_id', $doctor->id)
            ->where('details->type', ShortLink::TYPE_DOCTOR_SERVICE)
            ->get();

        $rows = [];
        foreach ($services as $service) {
            // اگر بخش به مطب خاصی محدود شده باشد، فقط همان مطب ها
            $servicePlaces = Place::whereHas('service', fn ($q) => $q->where('services.id', $service->id))->exists()
                ? $places->filter(fn (Place $p) => $p->service()->where('services.id', $service->id)->exists())
                : $places;

            foreach ($servicePlaces as $place) {
                $key = 's'.$service->id.'_p'.$place->id;
                $link = $links->first(fn (ShortLink $l) => (int) data_get($l->details, 'service_id') === $service->id
                    && (int) data_get($l->details, 'place_id') === $place->id);
                $setting = AppointmentSetting::resolveFor($doctor->id, $service->id, $place->id, true);

                $rows[] = [
                    'key' => $key,
                    'service_id' => $service->id,
                    'service_title' => $service->title,
                    'place_id' => $place->id,
                    'place_title' => $place->title,
                    'has_setting' => (bool) $setting,
                    'link_id' => $link?->id,
                    'url' => $link ? url('/s/'.$link->link_code) : null,
                    'visits' => (int) ($link?->number_visited ?? 0),
                ];

                if (! $link && blank($this->codes[$key] ?? null)) {
                    $this->codes[$key] = ShortLink::suggestDoctorServiceCode(
                        (string) $doctor->full_name,
                        (string) $service->title,
                        $places->count() > 1 ? (string) $place->title : null,
                    );
                }
            }
        }

        $this->rows = $rows;
    }

    public function render()
    {
        return view('user::livewire.admin.user.doctor-service-links');
    }
}
