<?php

namespace Modules\AppointmentUser\Livewire\Admin\FeedBack;

use App\Models\ShortLink;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\app\Models\FeedbackAnswer;
use Modules\AppointmentUser\app\Models\FeedbackForm;

class FeedbackAnswerList extends Component
{
    use WithPagination;

    #[Url]
    public $formId = '';

    // "appointmentUserId-formId" of the submission shown in the modal
    public ?string $opened = null;

    public function updatingFormId()
    {
        $this->resetPage();
        $this->opened = null;
    }

    public function open(string $key)
    {
        $this->opened = $key;
    }

    public function close()
    {
        $this->opened = null;
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

    /**
     * آخرین لینک نظرسنجی ارسال شده برای هر نوبت، کلید: شناسه نوبت.
     */
    private function sentLinks(iterable $appointmentIds)
    {
        return ShortLink::query()
            ->where('shortlinkable_type', 'feedBack')
            ->whereIn('shortlinkable_id', $appointmentIds)
            ->orderBy('id')
            ->get()
            ->groupBy('shortlinkable_id')
            ->map(fn ($links) => $links->first());
    }

    public function render()
    {
        $submissions = FeedbackAnswer::query()
            ->when($this->formId, fn ($q) => $q->where('feedback_form_id', $this->formId))
            ->select('appointment_user_id', 'feedback_form_id', DB::raw('MAX(created_at) as answered_at'))
            ->groupBy('appointment_user_id', 'feedback_form_id')
            ->orderByDesc('answered_at')
            ->paginate(10);

        $ids = $submissions->pluck('appointment_user_id');
        $appointments = AppointmentUser::with(['user', 'doctor', 'service', 'place'])
            ->withTrashed()
            ->whereIn('id', $ids)
            ->get()->keyBy('id');
        $forms = FeedbackForm::withTrashed()
            ->whereIn('id', $submissions->pluck('feedback_form_id'))
            ->get()->keyBy('id');
        $links = $this->sentLinks($ids);

        $modal = null;
        if ($this->opened) {
            [$appId, $formId] = array_pad(explode('-', $this->opened), 2, null);
            $appId = (int) $appId;
            $formId = (int) $formId;

            $answers = FeedbackAnswer::with('question')
                ->where('appointment_user_id', $appId)
                ->where('feedback_form_id', $formId)
                ->get()
                ->keyBy('feedback_form_question_id');

            if ($answers->isNotEmpty()) {
                $form = FeedbackForm::withTrashed()->with(['questions'])->find($formId);
                $app = $appointments->get($appId)
                    ?? AppointmentUser::with(['user', 'doctor', 'service', 'place'])->withTrashed()->find($appId);
                $link = $links->get($appId) ?? $this->sentLinks([$appId])->get($appId);

                $modal = [
                    'form' => $form,
                    'appointment' => $app,
                    'link' => $link,
                    'answered_at' => $answers->max('created_at'),
                    // سوال‌های فرم به ترتیب؛ سوال حذف‌شده‌ای که پاسخ دارد هم در انتها نشان داده میشود
                    'rows' => collect($form?->questions ?? [])
                        ->map(fn ($q) => ['question' => $q, 'answer' => $answers->get($q->id)])
                        ->concat(
                            $answers->reject(fn ($a) => collect($form?->questions ?? [])->contains('id', $a->feedback_form_question_id))
                                ->map(fn ($a) => ['question' => $a->question, 'answer' => $a])
                        ),
                ];
            }
        }

        return view('appointmentuser::livewire.admin.feed-back.feedback-answer-list', [
            'submissions' => $submissions,
            'appointments' => $appointments,
            'forms' => $forms,
            'links' => $links,
            'allForms' => FeedbackForm::orderBy('title')->get(),
            'modal' => $modal,
        ]);
    }
}
