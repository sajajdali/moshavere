<?php

namespace Modules\User\Livewire\Admin\User;

use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Service\app\Models\Service;
use Modules\User\Entities\User;
use Modules\User\Enum\UserMetaEnum;

#[Title('مدیریت پزشکان و بخش‌ها')]
class DoctorServiceManagement extends Component
{
    use WithPagination;

    #[Url]
    public string $activeTab = 'doctors';

    #[Url]
    public array $search = [
        'first_name' => '',
        'last_name' => '',
        'licence_status' => '',
    ];

    public array $licenceNumbers = [];

    public array $serviceApiCodes = [];

    public function updatedSearch(): void
    {
        $this->resetPage('doctorsPage');
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['doctors', 'services'], true)) {
            return;
        }

        $this->activeTab = $tab;
    }

    public function resetDoctorSearch(): void
    {
        $this->reset('search');
        $this->resetPage('doctorsPage');
    }

    public function saveLicence(int $doctorId): void
    {
        $doctor = User::role('پزشک')->findOrFail($doctorId);
        $this->authorize('update', $doctor);

        $validated = $this->validate([
            "licenceNumbers.$doctorId" => ['nullable', 'string', 'max:255'],
        ], [
            "licenceNumbers.$doctorId.max" => 'شماره نظام پزشکی نباید بیشتر از ۲۵۵ کاراکتر باشد.',
        ]);

        $licenceNumber = trim((string) ($validated['licenceNumbers'][$doctorId] ?? ''));
        $doctor->dr_licence_number = $licenceNumber;
        $this->licenceNumbers[$doctorId] = $licenceNumber;

        session()->flash('success', 'شماره نظام پزشکی با موفقیت به‌روزرسانی شد.');
    }

    public function saveServiceApiCode(int $serviceId): void
    {
        $service = Service::findOrFail($serviceId);
        $this->authorize('update', $service);

        $validated = $this->validate([
            "serviceApiCodes.$serviceId" => ['nullable', 'string', 'max:255'],
        ], [
            "serviceApiCodes.$serviceId.max" => 'کد API نباید بیشتر از ۲۵۵ کاراکتر باشد.',
        ]);

        $apiCode = trim((string) ($validated['serviceApiCodes'][$serviceId] ?? ''));
        $service->update(['api_code' => $apiCode]);
        $this->serviceApiCodes[$serviceId] = $apiCode;

        session()->flash('success', 'کد API بخش با موفقیت به‌روزرسانی شد.');
    }

    public function render()
    {
        $licenceMeta = function ($query): void {
            $query->where('meta_key', UserMetaEnum::LICENCE_NUMBER)
                ->whereRaw("TRIM(COALESCE(meta_value, '')) <> ''");
        };

        $baseDoctorQuery = User::query()->role('پزشک');
        $hasDoctorsWithoutLicence = (clone $baseDoctorQuery)
            ->whereDoesntHave('metas', $licenceMeta)
            ->exists();

        $doctors = $baseDoctorQuery
            ->with('services')
            ->when(filled($this->search['first_name'] ?? null), function ($query): void {
                $query->whereHas('metas', function ($metaQuery): void {
                    $metaQuery->where('meta_key', UserMetaEnum::FIRST_NAME)
                        ->where('meta_value', 'LIKE', '%'.trim($this->search['first_name']).'%');
                });
            })
            ->when(filled($this->search['last_name'] ?? null), function ($query): void {
                $query->whereHas('metas', function ($metaQuery): void {
                    $metaQuery->where('meta_key', UserMetaEnum::LAST_NAME)
                        ->where('meta_value', 'LIKE', '%'.trim($this->search['last_name']).'%');
                });
            })
            ->when(($this->search['licence_status'] ?? '') === 'has', fn ($query) => $query->whereHas('metas', $licenceMeta))
            ->when(($this->search['licence_status'] ?? '') === 'missing', fn ($query) => $query->whereDoesntHave('metas', $licenceMeta))
            ->orderByDesc('id')
            ->paginate(12, ['*'], 'doctorsPage');

        foreach ($doctors as $doctor) {
            $this->licenceNumbers[$doctor->id] ??= (string) ($doctor->dr_licence_number ?? '');
        }

        $hasServicesWithoutApiCode = Service::query()
            ->whereRaw("TRIM(COALESCE(api_code, '')) = ''")
            ->exists();

        $services = Service::query()
            ->withCount('user')
            ->orderBy('priority')
            ->orderBy('title')
            ->paginate(18, ['*'], 'servicesPage');

        foreach ($services as $service) {
            $this->serviceApiCodes[$service->id] ??= (string) ($service->api_code ?? '');
        }

        return view('user::livewire.admin.user.doctor-service-management', compact(
            'doctors',
            'services',
            'hasDoctorsWithoutLicence',
            'hasServicesWithoutApiCode',
        ));
    }
}
