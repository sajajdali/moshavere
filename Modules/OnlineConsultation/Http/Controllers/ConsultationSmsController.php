<?php

namespace Modules\OnlineConsultation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConsultationSmsController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('admin.consultation.sms-reminders.index', $request->query());
    }
}
