<?php

namespace Modules\OnlineConsultation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\OnlineConsultation\Models\VoipRequestLog;

class VoipLogController extends Controller
{
    public function index(Request $request)
    {
        $phone = $request->string('phone')->trim()->toString();
        $logs = VoipRequestLog::query()->when($phone !== '', fn ($q) => $q->where('phone', 'like', '%'.$phone.'%'))
            ->latest()->paginate(30)->withQueryString();
        return view('onlineconsultation::voip-logs', compact('logs', 'phone'));
    }
}
