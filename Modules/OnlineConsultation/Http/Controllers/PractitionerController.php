<?php

namespace Modules\OnlineConsultation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\AppointmentBillingRecord;
use Modules\OnlineConsultation\Services\AppointmentBillingService;
use Modules\User\Entities\User;

class PractitionerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->validate(['search' => 'nullable|string|max:100'])['search'] ?? '';
        $people = ConsultationPractitioner::with('user')
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('display_name', 'like', '%'.$search.'%')->orWhere('extension', 'like', '%'.$search.'%');
            }))->latest()->paginate(20)->withQueryString();

        return view('onlineconsultation::practitioners', compact('people', 'search'));
    }

    public function create(Request $request)
    {
        $person = new ConsultationPractitioner(['active' => true, 'kind' => 'doctor', 'availability' => 'offline']);
        if ($request->filled('user')) {
            $user = User::findOrFail($request->integer('user'));
            $existing = ConsultationPractitioner::where('user_id', $user->id)->first();
            if ($existing) {
                return redirect()->route('admin.consultation.practitioners.edit', $existing);
            }
            $person->user_id = $user->id;
            $person->display_name = $user->fullName;
        }

        return $this->form($person);
    }

    public function edit(int $practitioner)
    {
        return $this->form(ConsultationPractitioner::findOrFail($practitioner));
    }

    private function form(ConsultationPractitioner $person)
    {
        // Only load a selected account; search users by mobile/ID to avoid listing every patient.
        $selectedUser = $person->user;
        $account = request()->validate(['account' => 'nullable|string|min:3|max:50'])['account'] ?? '';
        $accounts = $account === '' ? collect() : User::where('mobile', 'like', '%'.$account.'%')->limit(10)->get();

        return view('onlineconsultation::practitioner-form', [
            'person' => $person, 'selectedUser' => $selectedUser,
            'accounts' => $accounts, 'account' => $account,
        ]);
    }

    public function store(Request $request)
    {
        $person = ConsultationPractitioner::create($this->validated($request));

        return redirect()->route('admin.consultation.practitioners.edit', $person)->with('success', 'پزشک / کارشناس اضافه شد.');
    }

    public function update(Request $request, int $practitioner, AppointmentBillingService $billingService)
    {
        $person = ConsultationPractitioner::findOrFail($practitioner);
        $person->update($this->validated($request, $person));

        // Older appointments have a zero payout snapshot until the manager defines the expert's rate.
        if ((int) $person->payout_hourly_rate > 0) {
            AppointmentBillingRecord::where('practitioner_id', $person->user_id)
                ->where('payout_hourly_rate_snapshot', 0)
                ->chunkById(100, function ($records) use ($person, $billingService) {
                    foreach ($records as $record) {
                        $record->update(['payout_hourly_rate_snapshot' => (int) $person->payout_hourly_rate]);
                        $billingService->refresh($record);
                    }
                });
        }

        return redirect()->route('admin.consultation.practitioners.edit', $person)->with('success', 'اطلاعات و دسترسی‌ها ذخیره شد.');
    }

    private function validated(Request $request, ?ConsultationPractitioner $person = null): array
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id'), Rule::unique('consultation_practitioners', 'user_id')->ignore($person?->id)],
            'display_name' => 'required|string|max:255',
            'kind' => ['required', Rule::in(['doctor', 'expert'])],
            'active' => 'required|boolean',
            'app_access' => 'required|boolean',
            'availability' => ['required', Rule::in(['ready', 'busy', 'offline'])],
            'extension' => ['nullable', 'regex:/^[0-9]{1,20}$/', Rule::unique('consultation_practitioners', 'extension')->ignore($person?->id)],
            'sip_username' => 'nullable|string|max:255', 'sip_secret' => 'nullable|string|max:1024',
            'clear_sip_secret' => 'sometimes|boolean',
            'payout_hourly_rate' => 'nullable|required_if:active,1|integer|min:0|max:1000000000',
            'duration_minutes' => 'nullable|integer|min:5|max:180',
            'notes' => 'nullable|string|max:3000',
        ], [], ['user_id' => 'شناسه کاربر', 'extension' => 'داخلی', 'display_name' => 'نام نمایشی', 'payout_hourly_rate' => 'هزینه ساعتی مشاور']);
        // An existing consultation profile must remain attached to its original account.
        if ($person && (int) $data['user_id'] !== (int) $person->user_id) {
            throw ValidationException::withMessages(['user_id' => 'حساب متصل به این پروفایل قابل تغییر نیست.']);
        }
        if ($request->boolean('clear_sip_secret')) {
            $data['sip_secret'] = null;
        } elseif (! $request->filled('sip_secret')) {
            unset($data['sip_secret']);
        }
        unset($data['clear_sip_secret']);
        if (! $request->boolean('active')) {
            $data['availability'] = 'offline';
            $data['app_access'] = false;
        }
        return $data;
    }
}
