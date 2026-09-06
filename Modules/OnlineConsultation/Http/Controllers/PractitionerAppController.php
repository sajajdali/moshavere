<?php

namespace Modules\OnlineConsultation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;

class PractitionerAppController extends Controller
{
    private function practitioner(Request $request): ConsultationPractitioner
    {
        abort_unless(ConsultationSetting::current()->app_enabled, 403);

        return ConsultationPractitioner::where('user_id', $request->user()->id)
            ->where('active', true)->where('app_access', true)->firstOrFail();
    }

    public function show(Request $request)
    {
        $person = $this->practitioner($request);

        return response()->json(['data' => $person->only([
            'id', 'display_name', 'kind', 'specialty', 'availability', 'weekly_schedule',
        ])]);
    }

    public function availability(Request $request)
    {
        $person = $this->practitioner($request);
        $data = $request->validate(['availability' => ['required', Rule::in(['ready', 'busy', 'offline'])]]);
        $person->update($data);

        return response()->json(['availability' => $person->availability]);
    }
}
