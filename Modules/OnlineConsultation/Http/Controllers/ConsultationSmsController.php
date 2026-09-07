<?php

namespace Modules\OnlineConsultation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\OnlineConsultation\Models\ConsultationSmsDelivery;

class ConsultationSmsController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->validate(['status' => ['nullable', 'in:pending,retrying,sent,failed,skipped']])['status'] ?? null;
        $deliveries = ConsultationSmsDelivery::with(['appointment.user', 'practitioner'])->when($status, fn ($q) => $q->where('status', $status))->latest('scheduled_at')->paginate(30)->withQueryString();

        return view('onlineconsultation::sms-deliveries', compact('deliveries', 'status'));
    }
}
