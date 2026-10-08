<?php

namespace Modules\User\Livewire\Admin\User\UserDocument;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Modules\Front\app\Models\FeedBack;
use Modules\Front\enum\FeedbackId;
use Modules\User\app\Models\PatientNote;
use Modules\User\Entities\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\Transaction\app\Models\Transaction;
use Modules\AppointmentUser\app\Models\FeedbackAnswer;
use Modules\AppointmentUser\app\Models\FeedbackForm;

class Index extends Component
{
    use AuthorizesRequests, WithPagination;

    public $user ;

    public array $fetchData = [];
    public array $form = [];

    // the patient information that can be edited from the top of the file
    public bool $editingProfile = false;
    public array $profile = [];

    public function addComment()
    {
        $this->validate([
            'form.comment' => 'required|string|max:5000',
        ]);
        PatientNote::create([
            'user_id'   => $this->user->id,
            'author_id' => auth()->id(),
            'body'      => trim($this->form['comment']),
        ]);
        $this->form['comment'] = '';
        $this->dispatch('showAlert', message: 'یادداشت ذخیره شد');
    }

    private function fillProfile(): void
    {
        $this->profile = [
            'first_name'      => (string) $this->user->first_name,
            'last_name'       => (string) $this->user->last_name,
            'mobile'          => (string) $this->user->mobile,
            'email'           => (string) $this->user->email,
            'national_code'   => (string) $this->user->national_code,
            'document_number' => (string) $this->user->document_number,
        ];
    }

    public function editProfile(): void
    {
        $this->authorize('update', $this->user);
        $this->resetValidation();
        $this->fillProfile();
        $this->editingProfile = true;
    }

    public function cancelEditProfile(): void
    {
        $this->resetValidation();
        $this->fillProfile();
        $this->editingProfile = false;
    }

    protected function rules(): array
    {
        return [
            'profile.first_name'      => 'nullable|string|max:100',
            'profile.last_name'       => 'nullable|string|max:100',
            'profile.mobile'          => 'required|digits:11|unique:users,mobile,' . $this->user->id,
            'profile.email'           => 'nullable|email|unique:users,email,' . $this->user->id,
            'profile.national_code'   => 'nullable|digits:10',
            'profile.document_number' => 'nullable|string|max:50',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'profile.first_name'      => 'نام',
            'profile.last_name'       => 'نام خانوادگی',
            'profile.mobile'          => 'موبایل',
            'profile.email'           => 'ایمیل',
            'profile.national_code'   => 'کد ملی',
            'profile.document_number' => 'شماره پرونده',
        ];
    }

    public function saveProfile(): void
    {
        $this->authorize('update', $this->user);
        $this->validate();

        $this->user->update([
            'mobile' => $this->profile['mobile'],
            'email'  => $this->profile['email'] ?: null,
        ]);
        // the profile fields are stored as user metas, their setters save immediately
        $this->user->first_name      = $this->profile['first_name'];
        $this->user->last_name       = $this->profile['last_name'];
        $this->user->national_code   = $this->profile['national_code'];
        $this->user->document_number = $this->profile['document_number'];

        $this->user = User::find($this->user->id);
        $this->fillProfile();
        $this->editingProfile = false;
        $this->dispatch('showAlert', message: 'اطلاعات بیمار با موفقیت ذخیره شد');
    }

    #[On('delete')]
    public function delete($model)
    {
        $note = PatientNote::where('user_id', $this->user->id)->findOrFail($model);
        // the author can remove his own note, otherwise the right to edit the patient is needed
        if ($note->author_id !== auth()->id()) {
            $this->authorize('update', $this->user);
        }
        $note->delete();

        $this->dispatch('showAlert', message: 'یادداشت با موفقیت حذف شد');
    }
    public function mount($user = null)
    {
        $user = $user ?? request()->route('user');
        $this->user = User::findOrFail($user instanceof User ? $user->getKey() : $user);
        $this->fillProfile();
    }

    /**
     * Answered feedback forms of the patient (new forms), newest first.
     */
    private function answeredForms($appointmentIds)
    {
        $answers = FeedbackAnswer::with('question')
            ->whereIn('appointment_user_id', $appointmentIds)
            ->get();
        $forms = FeedbackForm::withTrashed()
            ->whereIn('id', $answers->pluck('feedback_form_id')->unique())
            ->get()->keyBy('id');

        return $answers->groupBy(fn ($answer) => $answer->appointment_user_id . '-' . $answer->feedback_form_id)
            ->map(function ($group) use ($forms) {
                $first = $group->first();

                return [
                    'appointment_id' => $first->appointment_user_id,
                    'form'           => $forms->get($first->feedback_form_id)?->title ?? '-',
                    'answered_at'    => $group->max('created_at'),
                    'answers'        => $group->sortBy(fn ($answer) => $answer->question?->sort)->map(fn ($answer) => [
                        'question' => $answer->question?->title ?? '-',
                        'answer'   => \Modules\AppointmentUser\Livewire\Admin\FeedBack\FeedbackAnswerList::formatAnswer($answer),
                    ])->values(),
                ];
            })
            ->sortByDesc('answered_at')
            ->values();
    }

    /**
     * Answers of the first (fixed question) feedback, grouped by appointment.
     */
    private function legacyFeedbacks($appointmentIds)
    {
        return FeedBack::whereIn('appointment_user_id', $appointmentIds)->get()
            ->groupBy('appointment_user_id')
            ->map(function ($group, $appointmentId) {
                return [
                    'appointment_id' => $appointmentId,
                    'answered_at'    => $group->max('created_at'),
                    'answers'        => $group->map(function ($row) {
                        $question = FeedbackId::tryFrom((int) $row->question);

                        return [
                            'question' => $question?->getQuestion() ?? '-',
                            'answer'   => $question?->getQuestionChoises()[(int) $row->answer] ?? $row->answer,
                        ];
                    })->values(),
                ];
            })
            ->sortByDesc('answered_at')
            ->values();
    }

    private function financeEnabled(): bool
    {
        return class_exists(\Modules\Finance\Services\FinanceReportService::class)
            && \App\Support\TenantModuleAccess::enabled('Finance')
            && auth()->user()?->can('finance');
    }

    public function render()
    {
        $appointments = $this->user->appointments()
            ->with(['doctor', 'service', 'place', 'transaction'])
            ->orderByDesc('date_visit')->orderByDesc('start_time')
            ->paginate(10, ['*'], 'appointments_page');

        $payments = Transaction::with('transactionable')
            ->where('user_id', $this->user->id)
            ->latest('id')
            ->paginate(10, ['*'], 'payments_page');

        $appointmentIds = $this->user->appointments()->withTrashed()->pluck('id');

        // the financial summary of the Finance module (when the module is enabled and the user may see it)
        $finance = $this->financeEnabled();

        return view('user::livewire.admin.user.user-document.index', [
            'appointments'    => $appointments,
            'appointmentCount' => $this->user->appointments()->count(),
            'payments'        => $payments,
            // with the Finance module the total includes the payments registered by hand (minus the refunds)
            'paidTotal'       => $finance
                ? app(\Modules\Finance\Services\FinanceReportService::class)->totals(['user_id' => $this->user->id])->net
                : (int) Transaction::where('user_id', $this->user->id)
                    ->where('status', \Modules\Transaction\Enum\TransactionStatusEnum::SUCCESSFUL)
                    ->sum('total_cost'),
            'notes'           => PatientNote::with('author')->where('user_id', $this->user->id)->latest('id')->get(),
            'finance'         => $finance,
            'answeredForms'   => $this->answeredForms($appointmentIds),
            'legacyFeedbacks' => $this->legacyFeedbacks($appointmentIds),
        ]);
    }
}
