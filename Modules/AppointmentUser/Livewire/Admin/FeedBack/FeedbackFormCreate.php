<?php

namespace Modules\AppointmentUser\Livewire\Admin\FeedBack;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Modules\Place\app\Models\Place;
use Modules\Service\app\Models\Service;
use Modules\User\Entities\User;
use Modules\AppointmentUser\app\Models\FeedbackForm;
use Modules\AppointmentUser\app\Models\FeedbackFormQuestion;

class FeedbackFormCreate extends Component
{
    public array $form = [
        'title' => '',
        'doctor_id' => '',
        'service_id' => '',
        'place_id' => '',
        'active' => true,
    ];

    public array $questions = [];

    public function mount()
    {
        $this->addQuestion();
        // when there is only one choice, bind to it automatically and hide the select
        foreach (['doctor_id' => $this->doctors(), 'service_id' => $this->services(), 'place_id' => $this->places()] as $key => $items) {
            if ($items->count() === 1) {
                $this->form[$key] = $items->first()->id;
            }
        }
    }

    private function doctors()
    {
        return User::doctors_query()?->get() ?? collect();
    }

    private function services()
    {
        return Service::active()->get();
    }

    private function places()
    {
        return Place::active()->get();
    }

    public function addQuestion()
    {
        $this->questions[] = [
            'title' => '',
            'type' => FeedbackFormQuestion::TYPE_RADIO,
            'options' => '',
            'required' => true,
        ];
    }

    public function removeQuestion(int $index)
    {
        unset($this->questions[$index]);
        $this->questions = array_values($this->questions);
    }

    public function moveQuestion(int $index, int $direction)
    {
        $target = $index + $direction;
        if (! isset($this->questions[$target])) {
            return;
        }
        [$this->questions[$index], $this->questions[$target]] = [$this->questions[$target], $this->questions[$index]];
    }

    private function parseOptions(?string $options): array
    {
        return array_values(array_unique(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $options)))));
    }

    public function save()
    {
        $this->validate([
            'form.title' => 'required|string|max:255',
            'form.doctor_id' => 'nullable|exists:users,id',
            'form.service_id' => 'nullable|exists:services,id',
            'form.place_id' => 'nullable|exists:places,id',
            'questions' => 'required|array|min:1',
            'questions.*.title' => 'required|string|max:255',
            'questions.*.type' => 'required|in:' . implode(',', array_keys(FeedbackFormQuestion::types())),
        ], [
            'form.title.required' => 'عنوان نظرسنجی را وارد کنید',
            'questions.required' => 'حداقل یک سوال اضافه کنید',
            'questions.min' => 'حداقل یک سوال اضافه کنید',
            'questions.*.title.required' => 'متن سوال را وارد کنید',
        ]);

        foreach ($this->questions as $i => $question) {
            if (in_array($question['type'], FeedbackFormQuestion::typesWithOptions(), true)
                && count($this->parseOptions($question['options'])) < 2) {
                $this->addError("questions.$i.options", 'حداقل دو گزینه (هر گزینه در یک خط) وارد کنید');
                return;
            }
        }

        DB::transaction(function () {
            $feedbackForm = FeedbackForm::create([
                'title' => $this->form['title'],
                'doctor_id' => $this->form['doctor_id'] ?: null,
                'service_id' => $this->form['service_id'] ?: null,
                'place_id' => $this->form['place_id'] ?: null,
                'active' => (bool) $this->form['active'],
            ]);
            foreach ($this->questions as $i => $question) {
                $hasOptions = in_array($question['type'], FeedbackFormQuestion::typesWithOptions(), true);
                $feedbackForm->questions()->create([
                    'title' => $question['title'],
                    'type' => $question['type'],
                    'options' => $hasOptions ? $this->parseOptions($question['options']) : null,
                    'required' => (bool) $question['required'],
                    'sort' => $i,
                ]);
            }
        });

        return redirect()->route('admin.appointment.feedback.forms')->with('success', 'فرم نظرسنجی ذخیره شد');
    }

    public function render()
    {
        return view('appointmentuser::livewire.admin.feed-back.feedback-form-create', [
            'doctors' => $this->doctors(),
            'services' => $this->services(),
            'places' => $this->places(),
            'types' => FeedbackFormQuestion::types(),
            'optionTypes' => FeedbackFormQuestion::typesWithOptions(),
        ]);
    }
}
