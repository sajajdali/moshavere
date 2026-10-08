<?php

namespace Modules\AppointmentUser\Livewire\Admin\FeedBack;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\app\Models\FeedbackAnswer;
use Modules\AppointmentUser\app\Models\FeedbackForm;

class FeedbackAnswerList extends Component
{
    use WithPagination;

    #[Url]
    public $formId = '';

    // "appointmentUserId-formId" of the row whose answers are expanded
    public ?string $opened = null;

    public function updatingFormId()
    {
        $this->resetPage();
        $this->opened = null;
    }

    public function toggle(string $key)
    {
        $this->opened = $this->opened === $key ? null : $key;
    }

    public static function formatAnswer($answer): string
    {
        $decoded = json_decode((string) $answer->answer, true);
        if (is_array($decoded)) {
            return implode('، ', $decoded);
        }
        if ($answer->question?->type === 'rating') {
            return $answer->answer . ' از ۵';
        }
        return (string) $answer->answer;
    }

    public function render()
    {
        $submissions = FeedbackAnswer::query()
            ->when($this->formId, fn ($q) => $q->where('feedback_form_id', $this->formId))
            ->select('appointment_user_id', 'feedback_form_id', DB::raw('MAX(created_at) as answered_at'))
            ->groupBy('appointment_user_id', 'feedback_form_id')
            ->orderByDesc('answered_at')
            ->paginate(10);

        $appointments = AppointmentUser::with(['user', 'doctor', 'service', 'place'])
            ->withTrashed()
            ->whereIn('id', $submissions->pluck('appointment_user_id'))
            ->get()->keyBy('id');
        $forms = FeedbackForm::withTrashed()
            ->whereIn('id', $submissions->pluck('feedback_form_id'))
            ->get()->keyBy('id');

        $details = collect();
        if ($this->opened) {
            [$appId, $formId] = array_pad(explode('-', $this->opened), 2, null);
            $details = FeedbackAnswer::with('question')
                ->where('appointment_user_id', $appId)
                ->where('feedback_form_id', $formId)
                ->get()
                ->sortBy(fn ($a) => $a->question?->sort);
        }

        return view('appointmentuser::livewire.admin.feed-back.feedback-answer-list', [
            'submissions' => $submissions,
            'appointments' => $appointments,
            'forms' => $forms,
            'allForms' => FeedbackForm::orderBy('title')->get(),
            'details' => $details,
        ]);
    }
}
