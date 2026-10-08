<?php

namespace Modules\AppointmentUser\Livewire\Admin\FeedBack;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppointmentUser\app\Models\FeedbackForm;

class FeedbackFormList extends Component
{
    use WithPagination;

    public function toggleActive(int $id)
    {
        $form = FeedbackForm::findOrFail($id);
        $form->update(['active' => ! $form->active]);
    }

    public function deleteForm(int $id)
    {
        FeedbackForm::findOrFail($id)->delete();
        session()->flash('success', 'فرم نظرسنجی حذف شد');
    }

    public function render()
    {
        $forms = FeedbackForm::with(['doctor', 'service', 'place'])
            ->withCount(['questions', 'answers'])
            ->latest()
            ->paginate(10);

        return view('appointmentuser::livewire.admin.feed-back.feedback-form-list', ['forms' => $forms]);
    }
}
