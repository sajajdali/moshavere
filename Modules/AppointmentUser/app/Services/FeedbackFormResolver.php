<?php

namespace Modules\AppointmentUser\app\Services;

use Illuminate\Support\Collection;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\app\Models\FeedbackForm;

class FeedbackFormResolver
{
    /**
     * The most specific active form for the appointment, in this order:
     *  1. the appointment's doctor and service
     *  2. the doctor only (form without service)
     *  3. the service only (form without doctor)
     *  4. a global form (without doctor and service)
     * A form bound to another place never applies; inside a step a form of the appointment's own
     * place wins over a form without place, then the newest form wins.
     */
    public function resolve(AppointmentUser $appointment, ?Collection $forms = null): ?FeedbackForm
    {
        $forms ??= FeedbackForm::query()->where('active', true)->get();
        $forms = $forms->filter(fn (FeedbackForm $form) => empty($form->place_id) || (int) $form->place_id === (int) $appointment->place_id);

        $steps = [
            fn (FeedbackForm $f) => (int) $f->doctor_id === (int) $appointment->doctor_id && (int) $f->service_id === (int) $appointment->service_id,
            fn (FeedbackForm $f) => (int) $f->doctor_id === (int) $appointment->doctor_id && empty($f->service_id),
            fn (FeedbackForm $f) => empty($f->doctor_id) && (int) $f->service_id === (int) $appointment->service_id,
            fn (FeedbackForm $f) => empty($f->doctor_id) && empty($f->service_id),
        ];

        foreach ($steps as $matches) {
            $form = $forms->filter($matches)
                ->sortByDesc(fn (FeedbackForm $f) => [(int) ! empty($f->place_id), $f->id])
                ->first();
            if ($form) {
                return $form;
            }
        }

        return null;
    }
}
