<?php

namespace Modules\AppointmentSetting\Livewire\GeneralSetting;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\User\Entities\User;

#[Title('ترتیب نمایش پزشکان')]
class DoctorOrder extends Component
{
    /*
    the only place where the display order of doctors is changed;
    it is used by the add appointment page and the appointment settings doctor list
    through User::scopeDisplayOrder
    */
    public function mount()
    {
        $this->authorize('viewAny', AppointmentSetting::class);
    }

    private function orderedIds(): array
    {
        return User::doctors_query()?->displayOrder()->pluck('users.id')->map(fn ($id) => (int) $id)->all() ?? [];
    }

    private function save(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            foreach (array_values($ids) as $index => $id) {
                DB::table('users')->where('id', $id)->update(['display_order' => $index + 1]);
            }
        });
    }

    public function moveUp(int $id): void
    {
        $this->authorize('viewAny', AppointmentSetting::class);
        $ids = $this->orderedIds();
        $index = array_search($id, $ids, true);
        if ($index === false || $index === 0) {
            return;
        }
        [$ids[$index - 1], $ids[$index]] = [$ids[$index], $ids[$index - 1]];
        $this->save($ids);
    }

    public function moveDown(int $id): void
    {
        $this->authorize('viewAny', AppointmentSetting::class);
        $ids = $this->orderedIds();
        $index = array_search($id, $ids, true);
        if ($index === false || $index === count($ids) - 1) {
            return;
        }
        [$ids[$index + 1], $ids[$index]] = [$ids[$index], $ids[$index + 1]];
        $this->save($ids);
    }

    public function moveToTop(int $id): void
    {
        $this->authorize('viewAny', AppointmentSetting::class);
        $ids = $this->orderedIds();
        if (! in_array($id, $ids, true)) {
            return;
        }
        $this->save([$id, ...array_values(array_diff($ids, [$id]))]);
    }

    // drag and drop: the browser sends the whole list in its new order
    public function reorder(array $ids): void
    {
        $this->authorize('viewAny', AppointmentSetting::class);
        $ids = array_map('intval', $ids);
        $current = $this->orderedIds();
        if (count($ids) !== count($current) || array_diff($current, $ids) !== []) {
            return;
        }
        $this->save($ids);
    }

    public function render()
    {
        $doctors = User::doctors_query()?->displayOrder()->with(['metas', 'specialities'])->get() ?? collect();

        return view('appointmentsetting::livewire.general-setting.doctor-order', compact('doctors'));
    }
}
