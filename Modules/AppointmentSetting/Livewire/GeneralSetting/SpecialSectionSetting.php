<?php

namespace Modules\AppointmentSetting\Livewire\GeneralSetting;

use Carbon\Carbon;
use App\Enum\ActiveEnum;
use Livewire\Component;
use Livewire\Attributes\On;
use Modules\User\Entities\User;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentSetting\app\Enum\AppintmentSettingDayNumber;

class SpecialSectionSetting extends Component
{
    public array $form =  [];
    public array $fetchData =  [];
    public $doctor;

    public function editGeneralSetting()
    {
        session()->flash('resetTheSetting', true);
        return redirect()->route('admin.appointment.setting', ['user' => $this->doctor->id, 'edit' => 'true']);
    }
    private function fillTheFechData()
    {
        $this->fetchData['GeneralAppointmentSetting'] = AppointmentSetting::with('times')->where('user_id', $this->fetchData['user'])->whereNull('service_id')->first();
        $this->fetchData['SpecialAppointmentSetting'] = AppointmentSetting::with(['times', 'service', 'place'])->where('user_id', $this->fetchData['user'])->whereNotNull('service_id')->whereHas('service')->get();
    }

    /**
     * The weekly hours of a setting, Saturday to Friday; days without hours are closed.
     *
     * @return array<int, array{name: string, today: bool, ranges: array<int, string>}>
     */
    private function weekSchedule(AppointmentSetting $setting): array
    {
        $byDay = $setting->times->whereNull('special_date')
            ->sortBy('start_at')
            ->groupBy(fn ($time) => (int) $time->getRawOriginal('day_number'));
        $today = AppintmentSettingDayNumber::getConstant(strtolower(now()->format('l')));

        return collect(AppintmentSettingDayNumber::cases())->map(fn (AppintmentSettingDayNumber $day) => [
            'name' => $day->getName(),
            'today' => $day === $today,
            'ranges' => ($byDay[$day->value] ?? collect())
                ->map(fn ($time) => substr($time->start_at, 0, 5) . ' تا ' . substr($time->end_at, 0, 5))
                ->values()->all(),
        ])->all();
    }

    /**
     * Upcoming one-off dates with their own hours.
     *
     * @return array<int, array{date: string, ranges: array<int, string>}>
     */
    private function specialDates(AppointmentSetting $setting): array
    {
        return $setting->times->whereNotNull('special_date')
            ->filter(fn ($time) => Carbon::parse($time->special_date)->gte(today()))
            ->sortBy(['special_date', 'start_at'])
            ->groupBy(fn ($time) => Carbon::parse($time->special_date)->toDateString())
            ->map(fn ($times, $date) => [
                'date' => verta(Carbon::parse($date))->format('l j F'),
                'ranges' => $times->map(fn ($time) => substr($time->start_at, 0, 5) . ' تا ' . substr($time->end_at, 0, 5))->values()->all(),
            ])->values()->all();
    }

    /**
     * Short facts shown on each setting: status, visit length, booking window, visit types and online payment.
     */
    private function summary(AppointmentSetting $setting): array
    {
        $detail = (array) $setting->detail;
        $types = [
            AppointmentSetting::IN_PERSON => ['حضوری', AppointmentSetting::VISIT_TYPE_INPERSON],
            AppointmentSetting::VOIP => ['تلفنی', AppointmentSetting::VISIT_TYPE_VOIP],
            AppointmentSetting::ONLINE => ['آنلاین', AppointmentSetting::VISIT_TYPE_ONLINE],
        ];
        $enabledTypes = collect($types)->filter(fn ($type) => (bool) data_get($detail, $type[1]));

        return [
            'active' => $setting->active === ActiveEnum::ACTIVE,
            'duration' => $setting->time_for_visit ? $setting->time_for_visit . ' دقیقه' : null,
            'window' => $setting->max_day_active ? 'تا ' . $setting->max_day_active . ' روز آینده' : null,
            'visitTypes' => $enabledTypes->map(fn ($type) => $type[0])->values()->all(),
            'paymentOn' => (bool) data_get($detail, AppointmentSetting::PAYMENT . '.' . AppointmentSetting::STATUS),
            // online payment price of each enabled visit type, e.g. «حضوری ۱٬۰۰۰٬۰۰۰ ریال»;
            // prices of disabled visit types are leftovers and are not shown
            'payments' => $enabledTypes
                ->filter(fn ($type, $key) => data_get($detail, AppointmentSetting::PAYMENT . ".{$key}." . AppointmentSetting::STATUS))
                ->map(fn ($type, $key) => $type[0] . ' ' . number_format((int) data_get($detail, AppointmentSetting::PAYMENT . ".{$key}." . AppointmentSetting::PRICE)) . ' ریال')
                ->values()->all(),
        ];
    }

    #[On('docAndSection')]
    public function redirectToSetting($section, $doctor)
    {
        return redirect()->route('admin.appointment.setting.specialservice', [$doctor, $section]);
    }

    public function editSpecialSection($AppointmentSettingId)
    {
        $this->dispatch('redirectLoading',true);
        $appTime  = AppointmentSetting::find($AppointmentSettingId);
        $this->authorizeSettingOwner($appTime);
        session()->flash('resetTheSetting', true);
        return redirect()->route('admin.appointment.setting.specialservice', [$appTime->user_id, $appTime->service_id, $appTime->place_id]);
    }

    #[On('delete')]
    public function deleteSpecialSectionSetting($model)
    {
        $appTime  = AppointmentSetting::find($model);
        $this->authorizeSettingOwner($appTime);
        $appTime->times->map(function ($q) {
            $q->delete();
        });
        $appTime->delete();
        return redirect()->route('admin.appointment.specialsection', $this->doctor)->with('success', 'تنظیمات با موفقیت  حذف شد');
    }
    public function mount()
    {
        $this->fetchData['user'] = request()->route('user');
        $this->doctor = ! empty($this->fetchData['user']) ? User::find($this->fetchData['user']) : null;
        if (empty($this->doctor)) {
            return redirect()->route('admin.appointment.doctor.list')->with('error', 'پزشک مورد نظر یافت نشد');
        }
        $currentUser = auth()->user();
        if (! $currentUser->isAdmin() && $currentUser->can('AppointmentSetting.own') && (int) $this->doctor->id !== (int) $currentUser->id) {
            abort(403, 'Unauthorized');
        }
        $this->fillTheFechData();
    }

    private function authorizeSettingOwner(?AppointmentSetting $setting): void
    {
        abort_unless($setting, 404);
        $user = auth()->user();
        if (! $user->isAdmin() && $user->can('AppointmentSetting.own') && (int) $setting->user_id !== (int) $user->id) {
            abort(403, 'Unauthorized');
        }
    }

    public function render()
    {
        // relations are not kept across Livewire requests, so load them for each render
        $general = $this->fetchData['GeneralAppointmentSetting'] ?? null;
        $specials = collect($this->fetchData['SpecialAppointmentSetting'] ?? []);
        $general?->loadMissing('times');
        $specials->each->loadMissing(['times', 'service', 'place']);
        $this->doctor?->loadMissing(['metas', 'specialities']);

        return view('appointmentsetting::livewire.general-setting.special-section-setting', [
            'generalView' => $general ? [
                'facts' => $this->summary($general),
                'days' => $this->weekSchedule($general),
                'dates' => $this->specialDates($general),
            ] : null,
            'specialViews' => $specials->mapWithKeys(fn (AppointmentSetting $setting) => [$setting->id => [
                'facts' => $this->summary($setting),
                'days' => $this->weekSchedule($setting),
                'dates' => $this->specialDates($setting),
            ]])->all(),
        ]);
    }
}
