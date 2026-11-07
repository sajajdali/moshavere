<?php

namespace Modules\AppointmentSetting\Livewire\GeneralSetting\Modal;

use Livewire\Component;
use Modules\Place\app\Models\Place;
use Modules\Service\app\Models\Service;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

class ServiceAndPlaceModal extends Component
{
    public $search;
    public $step;
    public $doctor, $placeId;
    public array $fetchData = [];
    public array $form = [];
    public array $selectedServices = [];

    public function mount()
    {
        $this->resetSteps();
    }

    public function dismisMOdal()
    {
        $this->resetSteps();
        $this->search = null;
        $this->selectedServices = [];
    }

    public function searchDocAndSection()
    {
        // results are filtered in render() based on $search
    }

    public function ignoreSearch()
    {
        $this->search = null;
    }

    public function showRelatedSection($placeId)
    {
        $this->form['place'] = $placeId;
        $this->search = null;
        $this->selectedServices = [];
        $this->step = 2;
    }

    public function backToPlaces()
    {
        $this->search = null;
        $this->selectedServices = [];
        $this->step = 1;
    }

    public function toggleService($serviceId)
    {
        $serviceId = (int) $serviceId;
        if (in_array($serviceId, $this->selectedServices, true)) {
            $this->selectedServices = array_values(array_diff($this->selectedServices, [$serviceId]));
        } else {
            $this->selectedServices[] = $serviceId;
        }
    }

    public function clearSelection()
    {
        $this->selectedServices = [];
    }

    /**
     * Select (or unselect, if all are already selected) every service of a group.
     */
    public function toggleGroup(array $serviceIds)
    {
        $serviceIds = array_map('intval', $serviceIds);
        $allSelected = empty(array_diff($serviceIds, $this->selectedServices));
        if ($allSelected) {
            $this->selectedServices = array_values(array_diff($this->selectedServices, $serviceIds));
        } else {
            $this->selectedServices = array_values(array_unique(array_merge($this->selectedServices, $serviceIds)));
        }
    }

    /**
     * Split the doctor's services into standalone ones and groups of "parent + its sub-sections".
     * A parent the doctor isn't attached to still becomes a group header (not selectable).
     */
    private function groupServices($services): array
    {
        $byId = $services->keyBy('id');
        $childrenByParent = $services->whereNotNull('parent_id')->groupBy('parent_id');

        $missingParentIds = $childrenByParent->keys()->map(fn ($id) => (int) $id)
            ->reject(fn ($id) => $byId->has($id))->values();
        $parentTitles = $missingParentIds->isEmpty()
            ? collect()
            : Service::whereIn('id', $missingParentIds)->pluck('title', 'id');

        $groups = [];
        foreach ($childrenByParent as $parentId => $children) {
            $parentId = (int) $parentId;
            $parent = $byId->get($parentId);
            $groups[] = [
                'id' => $parentId,
                'title' => $parent?->title ?? $parentTitles->get($parentId, 'بدون والد'),
                'priority' => $parent?->priority ?? $children->min('priority'),
                // the parent itself is selectable only if the doctor is attached to it
                'parent' => $parent,
                'children' => $children->sortBy('priority')->values(),
            ];
        }
        usort($groups, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        $groupedIds = $childrenByParent->keys()->map(fn ($id) => (int) $id)->all();
        $standalone = $services
            ->whereNull('parent_id')
            ->reject(fn ($s) => in_array($s->id, $groupedIds, true))
            ->sortBy('priority')
            ->values();

        return ['standalone' => $standalone, 'groups' => $groups];
    }

    /**
     * Go to the special setting page for the selected services.
     * The first service is the route parameter (its existing setting is loaded into the form);
     * the rest are passed as ?services= and receive the same setting on save.
     */
    public function continueWithSelected()
    {
        $ids = $this->doctor->service()
            ->whereIn('services.id', array_map('intval', $this->selectedServices))
            ->pluck('services.id')
            ->all();
        // keep the order the user picked them in
        $ids = array_values(array_filter(array_map('intval', $this->selectedServices), fn ($id) => in_array($id, $ids)));

        if (empty($ids)) {
            $this->addError('selectedServices', 'لطفا حداقل یک بخش انتخاب کنید');
            return;
        }

        $params = [$this->doctor->id, $ids[0], $this->form['place'] ?? null];
        if (count($ids) > 1) {
            $params['services'] = implode(',', $ids);
        }
        session()->flash('resetTheSetting', true);
        return redirect()->route('admin.appointment.setting.specialservice', $params);
    }

    // kept for backward compatibility (single service selection)
    public function selectSection($serviceId)
    {
        $this->selectedServices = [(int) $serviceId];
        return $this->continueWithSelected();
    }

    private function resetSteps()
    {
        $placeCount = Place::count();
        $this->fetchData['multiPlace'] = $placeCount > 1;
        if ($placeCount > 1) {
            $this->step = 1;
            unset($this->form['place']);
        } else {
            $this->form['place'] = Place::value('id');
            $this->step = 2;
        }
    }

    public function handelSearch()
    {
        if ($this->step == 1) {
            return Place::when(filled($this->search), function ($query) {
                return $query->where('title', 'LIKE', "%{$this->search}%");
            })->get();
        }
        return $this->doctor->service()->active()->when(filled($this->search), function ($query) {
            return $query->where('title', 'LIKE', "%{$this->search}%");
        })->orderBy('priority')->get();
    }

    public function render()
    {
        $servicesWithSpecialSetting = [];
        $selectedTitles = [];
        $grouped = ['standalone' => collect(), 'groups' => []];
        $items = $this->handelSearch();
        if ($this->step == 2) {
            $grouped = $this->groupServices($items);
            $servicesWithSpecialSetting = AppointmentSetting::where('user_id', $this->doctor->id)
                ->whereNotNull('service_id')
                ->when(
                    filled($this->form['place'] ?? null),
                    fn ($q) => $q->where('place_id', $this->form['place']),
                    fn ($q) => $q->whereNull('place_id'),
                )
                ->pluck('service_id')
                ->map(fn ($id) => (int) $id)
                ->all();
            if (!empty($this->selectedServices)) {
                $selectedTitles = $this->doctor->service()
                    ->whereIn('services.id', $this->selectedServices)
                    ->pluck('services.title', 'services.id')
                    ->all();
            }
        }

        return view(
            'appointmentsetting::livewire.general-setting.modal.service-and-place-modal',
            [
                'ServiceOrPlace' => $items,
                'grouped' => $grouped,
                'servicesWithSpecialSetting' => $servicesWithSpecialSetting,
                'selectedTitles' => $selectedTitles,
            ]
        );
    }
}
