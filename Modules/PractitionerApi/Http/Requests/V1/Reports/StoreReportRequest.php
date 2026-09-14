<?php

namespace Modules\PractitionerApi\Http\Requests\V1\Reports;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\OnlineConsultation\Models\AppointmentConsultationReport;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'outcome' => ['required', Rule::in(array_keys(AppointmentConsultationReport::OUTCOMES))],
            'subject' => ['required', 'string', 'min:3', 'max:200'],
            'report_text' => ['required', 'string', 'min:10', 'max:10000'],
            'follow_up_at' => ['required', 'date'],
        ];
    }
}
