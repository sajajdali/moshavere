<?php

namespace Modules\Api\Http\Controllers\Appointment;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Api\app\Resources\Api\Appointments\DoctorResource;
use Modules\Api\app\Resources\Api\PlaceResource;
use Modules\Api\app\Resources\Api\ServiceResource;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\User\Entities\User;

class AppointmentApiController extends Controller
{
    use ApiHandlerTrait;
    public function doctorsList()
    {
        $user = auth()->user();
        $doctors = User::doctors(onlyActiveAppointment: true);

        return $this->ok([
                'status' => true,
                'name'  => $user->id,
                'doctors'  => DoctorResource::collection($doctors)
            ]
        );
    }

    public function places(User $doctor)
    {
        $places = $doctor->places()->Active()->orderBy('priority')->get();
        return $this->ok([
                'status' => true,
                'places'  =>  PlaceResource::collection($places)
            ]
        );
    }

    public function services(User $doctor)
    {
        $services = ServiceResource::collection($doctor->services()->Active()->orderBy('priority')->get());

        return $this->ok([
                'status' => true,
                'services'  => $services
            ]
        );
    }

    public function listDays(Request $request)
    {
        $doctorId = $request->input('doctor_id');
        $placesId = $request->input('places_id');
        $servicesId = $request->input('services_id');

        $appointmentSetting = AppointmentSetting::where('user_id', $doctorId);
        if ($placesId){
            $appointmentSetting->where('place_id', $placesId);
        }
        if ($servicesId){
            $appointmentSetting->where('service_id', $servicesId);
        }
        $appointmentSetting = $appointmentSetting->get();

        return $this->ok([
            'status' => true,
            'setting' => $appointmentSetting
        ]);
    }

}
