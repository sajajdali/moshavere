<?php

namespace Modules\Api\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\app\Models\FeedbackAnswer;
use Modules\AppointmentUser\app\Models\FeedbackForm;
use Modules\AppointmentUser\app\Models\FeedbackFormQuestion;
use Modules\AppointmentUser\app\Services\FeedbackFormResolver;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;

/**
 * نظرسنجی بیمار در قالب جدید.
 *
 * بدون ورود قابل استفاده است؛ دسترسی فقط با کد پیگیری نوبت (کد تصادفی ۸ رقمی)
 * انجام میشود، نه شناسه ترتیبی نوبت، تا با حدس زدن آدرس به نوبت دیگران نرسند (IDOR).
 * برای هر کد نامعتبر یا نوبت نامناسب، پاسخ یکسان ۴۰۴ داده میشود تا وجود کد لو نرود.
 */
class TenantFeedbackController extends Controller
{
    use ApiHandlerTrait;

    public function show(string $trackingCode, FeedbackFormResolver $resolver): \Illuminate\Http\JsonResponse
    {
        $appointment = $this->findAppointment($trackingCode);
        if (! $appointment) {
            return $this->notFound();
        }

        $form = $resolver->resolve($appointment);
        $answered = $this->alreadyAnswered($appointment);

        if (! $form && ! $answered) {
            return $this->notFound();
        }

        $form?->load('questions');

        return $this->ok([
            'status' => true,
            'answered' => $answered,
            'appointment' => [
                'doctor' => $appointment->doctor?->full_name,
                'service' => $appointment->service?->title,
                'date' => $appointment->date_visit ? verta($appointment->date_visit)->format('l Y/m/d') : null,
            ],
            'form' => $answered || ! $form ? null : [
                'title' => $form->title,
                'questions' => $form->questions->map(fn (FeedbackFormQuestion $q) => [
                    'id' => $q->id,
                    'title' => $q->title,
                    'type' => $q->type,
                    'options' => $q->options ?? [],
                    'required' => (bool) $q->required,
                ])->values(),
            ],
        ]);
    }

    public function store(string $trackingCode, Request $request, FeedbackFormResolver $resolver): \Illuminate\Http\JsonResponse
    {
        $appointment = $this->findAppointment($trackingCode);
        if (! $appointment) {
            return $this->notFound();
        }

        $form = $resolver->resolve($appointment);
        if (! $form) {
            return $this->notFound();
        }

        $input = $request->input('answers', []);
        if (! is_array($input)) {
            return $this->badRequest(['message' => 'پاسخ ها نامعتبر است.']);
        }

        $rows = [];
        $errors = [];
        foreach ($form->questions as $question) {
            $value = $input[$question->id] ?? null;
            [$ok, $clean, $message] = $this->cleanAnswer($question, $value);
            if (! $ok) {
                $errors[$question->id] = $message;
                continue;
            }
            if ($clean !== null) {
                $rows[] = [$question->id, $clean];
            }
        }

        if ($errors) {
            return response()->json(['message' => 'لطفا به پرسش های الزامی پاسخ دهید.', 'errors' => $errors], 422);
        }

        // ثبت دوبار (دابل کلیک یا درخواست هم زمان) ممکن نباشد
        $saved = DB::transaction(function () use ($appointment, $form, $rows) {
            AppointmentUser::whereKey($appointment->id)->lockForUpdate()->first();
            if ($this->alreadyAnswered($appointment)) {
                return false;
            }
            foreach ($rows as [$questionId, $answer]) {
                FeedbackAnswer::create([
                    'appointment_user_id' => $appointment->id,
                    'feedback_form_id' => $form->id,
                    'feedback_form_question_id' => $questionId,
                    'answer' => $answer,
                ]);
            }

            return true;
        });

        if (! $saved) {
            return response()->json(['message' => 'نظر شما قبلا ثبت شده است.', 'answered' => true], 409);
        }

        return $this->ok(['status' => true, 'message' => 'نظر شما ثبت شد. سپاس از همراهی شما.']);
    }

    /**
     * فقط نوبت حضوری/آنلاینی که انجام شده یا تایید شده و تاریخ آن گذشته (یا امروز) است.
     */
    private function findAppointment(string $trackingCode): ?AppointmentUser
    {
        if (! preg_match('/^[A-Za-z0-9_-]{4,32}$/', $trackingCode)) {
            return null;
        }

        $appointment = AppointmentUser::with(['doctor', 'service'])
            ->where('tracking_code', $trackingCode)
            ->first();

        if (! $appointment) {
            return null;
        }

        $allowed = [
            AppointmentUserStatusEnum::STATUS_SUCCESSFUL,
            AppointmentUserStatusEnum::STATUS_ATTENDED,
            AppointmentUserStatusEnum::STATUS_ONILNE_CLOSED,
        ];
        if (! in_array($appointment->status, $allowed, true)) {
            return null;
        }

        if ($appointment->date_visit && $appointment->date_visit->isFuture() && ! $appointment->date_visit->isToday()) {
            return null;
        }

        return $appointment;
    }

    private function alreadyAnswered(AppointmentUser $appointment): bool
    {
        return FeedbackAnswer::where('appointment_user_id', $appointment->id)->exists()
            || $appointment->feedbacks()->exists();
    }

    /**
     * @return array{0: bool, 1: ?string, 2: ?string} [معتبر؟، مقدار قابل ذخیره، پیام خطا]
     */
    private function cleanAnswer(FeedbackFormQuestion $question, mixed $value): array
    {
        $empty = $value === null || $value === '' || $value === [];
        if ($empty) {
            return $question->required ? [false, null, 'این پرسش الزامی است.'] : [true, null, null];
        }

        $options = $question->options ?? [];

        switch ($question->type) {
            case FeedbackFormQuestion::TYPE_TEXT:
            case FeedbackFormQuestion::TYPE_TEXTAREA:
                if (! is_string($value)) {
                    return [false, null, 'پاسخ نامعتبر است.'];
                }
                $limit = $question->type === FeedbackFormQuestion::TYPE_TEXT ? 255 : 2000;

                return [true, mb_substr(trim(strip_tags($value)), 0, $limit), null];

            case FeedbackFormQuestion::TYPE_SELECT:
            case FeedbackFormQuestion::TYPE_RADIO:
                return is_string($value) && in_array($value, $options, true)
                    ? [true, $value, null]
                    : [false, null, 'گزینه انتخاب شده نامعتبر است.'];

            case FeedbackFormQuestion::TYPE_CHECKBOX:
                if (! is_array($value) || array_diff($value, $options) !== []) {
                    return [false, null, 'گزینه های انتخاب شده نامعتبر است.'];
                }

                return [true, json_encode(array_values(array_unique($value)), JSON_UNESCAPED_UNICODE), null];

            case FeedbackFormQuestion::TYPE_RATING:
                $rate = filter_var($value, FILTER_VALIDATE_INT);

                return $rate !== false && $rate >= 1 && $rate <= 5
                    ? [true, (string) $rate, null]
                    : [false, null, 'امتیاز باید بین ۱ تا ۵ باشد.'];
        }

        return [false, null, 'نوع پرسش نامعتبر است.'];
    }
}
