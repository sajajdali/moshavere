<?php

namespace Modules\AppointmentUser\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

class AppointmentUserController extends Controller
{

    public function test()
    {

//        Cache::forget('appointmentList.1');
//        dd(Cache::has('appointmentList.1'));
        $listUsers = Cache::rememberForever('appointmentList.1', function () {
            return app('AppointmentUserService')->listAppointments(AppointmentSetting::find(1));
        });

//        $updateOneDay = app('AppointmentUserService')->listAppointments(2, null, null, ['specialDay' => Carbon::today()->toDateString()]);
//        $dateSelect = verta(Carbon::today());
//        Cache::forget('appointmentList_2');
//        $listUsers[$dateSelect->year][$dateSelect->month][$dateSelect->day] = $updateOneDay[$dateSelect->year][$dateSelect->month][$dateSelect->day];
//        Cache::rememberForever('appointmentList_2', function () use ($listUsers) {
//            return $listUsers;
//        });

        // Add required days and remove unnecessary days from the log
        $lastDayActive = Carbon::parse($listUsers['report']['last_day'])->diffInDays(Carbon::now());

        // Add required days and remove unnecessary days from the log

          return $listUsers;
    }
    /**
     * Display a listing of the resource.
     */
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
