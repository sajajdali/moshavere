<?php

namespace Modules\Finance\Livewire;

use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\Finance\app\Models\FinancePayment;
use Modules\Finance\app\Models\FinancePaymentPurpose;
use Modules\Finance\Enum\FinancePaymentMethod;
use Modules\Finance\Enum\FinancePaymentType;
use Modules\Finance\Livewire\Concerns\InteractsWithFinanceFilters;
use Modules\Finance\Services\FinanceReportService;
use Modules\User\Entities\User;

/**
 * The modal that registers (or edits) a manual payment. It is opened from the pages with the
 * `finance-open-form` event: userId locks the patient, paymentId edits an existing payment.
 */
class PaymentForm extends Component
{
    use InteractsWithFinanceFilters;

    public ?int $paymentId = null;

    public ?int $userId = null;

    public bool $patientLocked = false;

    public string $patientSearch = '';

    public array $form = [];

    private function blankForm(): array
    {
        return [
            'type' => FinancePaymentType::PAYMENT->value,
            'method' => FinancePaymentMethod::POS->value,
            'purpose_id' => '',
            'amount' => '',
            'paid_date' => Verta::now()->format('Y/m/d'),
            'paid_time' => now()->format('H:i'),
            'reference_number' => '',
            'appointment_user_id' => '',
            'doctor_id' => '',
            'description' => '',
        ];
    }

    #[On('finance-open-form')]
    public function open(?int $userId = null, ?int $paymentId = null): void
    {
        $this->resetValidation();
        $this->reset('paymentId', 'userId', 'patientLocked', 'patientSearch');
        $this->form = $this->blankForm();

        if ($paymentId) {
            $this->authorizeFinance('finance.edit');
            $payment = FinancePayment::findOrFail($paymentId);
            $this->paymentId = $payment->id;
            $this->userId = $payment->user_id;
            $this->patientLocked = true;
            $this->form = [
                'type' => $payment->type->value,
                'method' => $payment->method->value,
                'purpose_id' => (string) $payment->purpose_id,
                // the input groups the digits by three (see the form view); the commas are removed on save
                'amount' => number_format($payment->amount),
                'paid_date' => Verta::instance($payment->paid_at)->format('Y/m/d'),
                'paid_time' => $payment->paid_at->format('H:i'),
                'reference_number' => (string) $payment->reference_number,
                'appointment_user_id' => (string) $payment->appointment_user_id,
                'doctor_id' => (string) $payment->doctor_id,
                'description' => (string) $payment->description,
            ];
        } else {
            $this->authorizeFinance('finance.create');
            $this->userId = $userId;
            $this->patientLocked = (bool) $userId;
        }

        $this->sendAppointmentOptions();
        $this->dispatch('finance-form-show');
    }

    /**
     * The appointment select is a select2 that Livewire does not morph (wire:ignore), so the appointments of
     * the patient are sent to the browser as an event whenever the patient (or the opened payment) changes.
     */
    private function sendAppointmentOptions(): void
    {
        $options = $this->userId
            ? AppointmentUser::with(['service', 'doctor'])->where('user_id', $this->userId)->orderByDesc('date_visit')->limit(100)->get()
                ->map(fn ($appointment) => [
                    'id' => (string) $appointment->id,
                    'text' => verta($appointment->date_visit)->format('Y/m/d')
                        . ' — ' . ($appointment->service?->title ?? 'بدون بخش')
                        . ($appointment->doctor ? ' — ' . $appointment->doctor->fullName : ''),
                ])->values()->all()
            : [];

        $this->dispatch('finance-appointments',
            options: $options,
            selected: (string) ($this->form['appointment_user_id'] ?? ''),
            hasPatient: (bool) $this->userId,
        );
    }

    public function selectPatient(int $id): void
    {
        if ($this->patientLocked) {
            return;
        }
        $this->userId = User::findOrFail($id)->id;
        $this->patientSearch = '';
        $this->form['appointment_user_id'] = '';
        $this->sendAppointmentOptions();
    }

    public function clearPatient(): void
    {
        if (! $this->patientLocked) {
            $this->userId = null;
            $this->form['appointment_user_id'] = '';
            $this->sendAppointmentOptions();
        }
    }

    /** the doctor of the chosen appointment is suggested */
    public function updatedFormAppointmentUserId($value): void
    {
        if ($value && ! $this->form['doctor_id']) {
            $this->form['doctor_id'] = (string) AppointmentUser::where('user_id', $this->userId)->whereKey($value)->value('doctor_id');
        }
    }

    /** digits written in Persian / Arabic or with separators become a plain number */
    private function cleanAmount(?string $value): string
    {
        $value = strtr((string) $value, ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);

        return preg_replace('/[\s,٬،.]/u', '', $value);
    }

    protected function rules(): array
    {
        return [
            'userId' => 'required|integer|exists:users,id',
            'form.type' => ['required', Rule::in(array_keys(FinancePaymentType::options()))],
            'form.method' => ['required', Rule::in(array_keys(FinancePaymentMethod::options()))],
            'form.purpose_id' => 'nullable|exists:finance_payment_purposes,id',
            'form.amount' => 'required|numeric|integer|min:1|max:100000000000',
            'form.paid_date' => ['required', function ($attribute, $value, $fail) {
                if (! $this->toGregorian($value)) {
                    $fail('تاریخ پرداخت معتبر نیست.');
                }
            }],
            'form.paid_time' => ['required', 'regex:/^([01]?\d|2[0-3]):[0-5]\d$/'],
            'form.reference_number' => 'nullable|string|max:100',
            'form.appointment_user_id' => ['nullable', Rule::exists('appointment_users', 'id')->where('user_id', $this->userId)],
            'form.doctor_id' => 'nullable|exists:users,id',
            'form.description' => 'nullable|string|max:2000',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'userId' => 'بیمار',
            'form.type' => 'نوع',
            'form.method' => 'روش پرداخت',
            'form.purpose_id' => 'دلیل پرداخت',
            'form.amount' => 'مبلغ',
            'form.paid_date' => 'تاریخ پرداخت',
            'form.paid_time' => 'ساعت پرداخت',
            'form.reference_number' => 'شماره پیگیری',
            'form.appointment_user_id' => 'نوبت',
            'form.doctor_id' => 'پزشک',
            'form.description' => 'توضیحات',
        ];
    }

    protected function messages(): array
    {
        return [
            'userId.required' => 'لطفا بیمار را انتخاب کنید.',
            'form.amount.required' => 'مبلغ را وارد کنید.',
            'form.amount.numeric' => 'مبلغ باید عدد باشد.',
            'form.amount.integer' => 'مبلغ باید عدد صحیح باشد.',
            'form.amount.min' => 'مبلغ باید بیشتر از صفر باشد.',
            'form.paid_time.regex' => 'ساعت را به شکل 14:30 وارد کنید.',
            'form.appointment_user_id.exists' => 'این نوبت متعلق به این بیمار نیست.',
        ];
    }

    public function save(): void
    {
        $this->authorizeFinance($this->paymentId ? 'finance.edit' : 'finance.create');
        // the amount comes with the commas of the input: they are removed for the validation and the save,
        // and put back when the validation fails so the field keeps showing what the user typed
        $typedAmount = $this->form['amount'] ?? '';
        $this->form['amount'] = $this->cleanAmount($typedAmount);
        $this->form['paid_time'] = trim(strtr((string) $this->form['paid_time'], ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9']));
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->form['amount'] = $typedAmount;

            throw $e;
        }

        $data = [
            'user_id' => $this->userId,
            'type' => $this->form['type'],
            'method' => $this->form['method'],
            'purpose_id' => $this->form['purpose_id'] ?: null,
            'amount' => (int) $this->form['amount'],
            'paid_at' => Carbon::parse($this->toGregorian($this->form['paid_date']) . ' ' . $this->form['paid_time']),
            'reference_number' => trim((string) $this->form['reference_number']) ?: null,
            'appointment_user_id' => $this->form['appointment_user_id'] ?: null,
            'doctor_id' => $this->form['doctor_id'] ?: null,
            'description' => trim((string) $this->form['description']) ?: null,
        ];

        if ($this->paymentId) {
            FinancePayment::findOrFail($this->paymentId)->update($data);
            $message = 'پرداخت ویرایش شد';
        } else {
            FinancePayment::create($data + ['created_by' => auth()->id()]);
            $message = 'پرداخت ثبت شد';
        }

        $this->dispatch('payment-saved');
        $this->dispatch('finance-form-hide');
        $this->dispatch('showAlert', message: $message);
    }

    public function render()
    {
        $patient = $this->userId ? User::find($this->userId) : null;
        $results = collect();
        if (! $patient && mb_strlen(trim($this->patientSearch)) >= 2) {
            $ids = app(FinanceReportService::class)->patientIdsMatching($this->patientSearch)->take(8);
            $results = User::whereIn('id', $ids)->get();
        }

        return view('finance::livewire.payment-form', [
            'patient' => $patient,
            'results' => $results,
            'methods' => FinancePaymentMethod::options(),
            'types' => FinancePaymentType::options(),
            'purposes' => FinancePaymentPurpose::ordered()->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $this->form['purpose_id'] ?? 0))->get(),
            'doctors' => User::doctors_query()?->get() ?? collect(),
            'amountNumber' => (int) $this->cleanAmount($this->form['amount'] ?? ''),
        ]);
    }
}
