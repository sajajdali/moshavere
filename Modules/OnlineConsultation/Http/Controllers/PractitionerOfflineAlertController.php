<?php

namespace Modules\OnlineConsultation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\OnlineConsultation\Models\PractitionerOfflineAlert;

class PractitionerOfflineAlertController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->can('SUPER_ADMIN'), 403);
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['accepted', 'failed', 'check_failed'])],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $alerts = PractitionerOfflineAlert::with(['appointment.user', 'practitioner.user'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(fn ($query) => $query
                    ->where('phone', 'like', '%'.$search.'%')
                    ->orWhere('extension', 'like', '%'.$search.'%')
                    ->orWhereHas('practitioner', fn ($profile) => $profile->where('display_name', 'like', '%'.$search.'%')));
            })
            ->latest('checked_at')->paginate(20)->withQueryString();

        return view('onlineconsultation::practitioner-offline-alerts', compact('alerts', 'filters'));
    }
}
