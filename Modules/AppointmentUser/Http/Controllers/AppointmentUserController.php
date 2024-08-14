<?php

namespace Modules\AppointmentUser\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

class AppointmentUserController extends Controller
{

    public function test()
    {



//        return  app('AppointmentUserService')->listAppointments(AppointmentSetting::find(1));


        $appointmentSetting = AppointmentSetting::find(1);

        if (env('APPOINTMENT_SANDBOX')) {
            Cache::forget('appointmentList.' . $appointmentSetting->id);
        }
        $listUsers = Cache::rememberForever('appointmentList.'.$appointmentSetting->id, function () use ($appointmentSetting) {
            $appointmentSetting->update(['updated_log_at' => \now()]);
            return app('AppointmentUserService')->listAppointments($appointmentSetting);
        });
//        $listUsers = app('AppointmentUserService')->listAppointments($appointmentSetting, ['specialDay' => Carbon::today()->toDateString()]);

//        $updateOneDay = app('AppointmentUserService')->listAppointments(2, null, null, ['specialDay' => Carbon::today()->toDateString()]);
//        $dateSelect = verta(Carbon::today());
//        Cache::forget('appointmentList_2');
//        $listUsers[$dateSelect->year][$dateSelect->month][$dateSelect->day] = $updateOneDay[$dateSelect->year][$dateSelect->month][$dateSelect->day];
//        Cache::rememberForever('appointmentList_2', function () use ($listUsers) {
//            return $listUsers;
//        });

        // Add required days and remove unnecessary days from the log
//        $lastDayActive = Carbon::parse($listUsers['report']['last_day'])->diffInDays(Carbon::now());

        // Add required days and remove unnecessary days from the log

          return $listUsers;
    }
    /**
     * Display a listing of the resource.
     */

     public function upload(Request $request) {
        $fileName = time().'.'.$request->file->extension();
        $filePath = '/online-message/voice';
        $file_location = $request->file->storeAs($filePath, $fileName);
        // $request->file->move(public_path('uploads/voice/'), $fileName);
        return ($file_location);
     }
    public function index()
    {
        return view('appointmentuser::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('appointmentuser::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('appointmentuser::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('appointmentuser::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
