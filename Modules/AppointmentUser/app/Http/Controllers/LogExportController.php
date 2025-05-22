<?php

namespace Modules\AppointmentUser\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\Place\app\Models\Place;
use Response;

class LogExportController extends Controller
{
    public function exportAppointmentsLog(Request $request)
    {
        $lastUpdate = $request->input('last_update', 0);
        $date = $request->has('date') ? Carbon::createFromFormat('Y/m/d', $request->input('date')) : null;

        $result = [
            'last_update' => (int) $lastUpdate,
            'offices' => [],
        ];

        Place::with(['users' => function ($q) {
            $q->whereHas('roles', fn($q) => $q->where('name', 'doctor'))
                ->with(['specialities', 'services']);
        }])->chunk(50, function ($places) use (&$result, $lastUpdate, $date) {
            foreach ($places as $place) {
                $office = [
                    'id' => $place->id,
                    'name' => $place->title,
                    'address' => $place->detail['address'] ?? '',
                    'longitude' => $place->detail['location_lng'] ?? null,
                    'latitude' => $place->detail['location_lat'] ?? null,
                    'insurance' => '',
                    'phone' => $place->detail['numbers'][0] ?? '',
                    'type' => ['title' => 'حضوری', 'color' => '#0480ff'],
                    'status' => [
                        'title' => $place->checkActive() ? 'فعال' : 'غیرفعال',
                        'color' => $place->checkActive() ? '#30d857' : '#ff0000',
                    ],
                    'doctors' => [],
                ];


                foreach ($place->first()->users as $doctor) {
                    $doctorItem = [
                        'id' => $doctor->id,
                        'full_name' => $doctor->full_name,
                        'city' => null,
                        'rate' => 5,
                        'avatar' => $doctor->avatar,
                        'specialty' => [
                            'id' => optional($doctor->specialities->first())->id,
                            'name' => optional($doctor->specialities->first())->title,
                        ],
                        'medical_code' => $doctor->dr_licence_number ?? '',
                        'is_device' => false,
                        'parts' => [],
                    ];

                    foreach ($doctor->services as $service) {
                        $part = [
                            'id' => $service->id,
                            'title' => $service->title,
                            'color' => '#dddddd',
                            'image_url' => $service->icon,
                            'selakteb_code' => $service->api_code,
                            'children' => [],
                            'appointment_users' => [],
                        ];

                        $appointmentsQuery = $doctor->doctorAppointments()
                            ->where('place_id', $place->id)
                            ->where('service_id', $service->id)
                            ->where('status', '!=', AppointmentUserStatusEnum::STATUS_SUCCESSFUL)
                            ->with(['user', 'transaction']);

                        if ($date) {
                            $endDate = (clone $date)->addDay();
                            $appointmentsQuery->whereBetween('date_visit', [$date->toDateString(), $endDate->toDateString()]);
                        } elseif ($lastUpdate) {
                            $appointmentsQuery->where('updated_at', '>', Carbon::createFromTimestamp($lastUpdate));
                        }

                        $appointments = $appointmentsQuery->get();

                        foreach ($appointments as $appointment) {
                            $user = $appointment->self_appointment ? $appointment->user : $appointment->agent;
                            $transaction = $appointment->transaction;

                            $part['appointment_users'][] = [
                                'id' => $appointment->id,
                                'user' => [
                                    'first_name' => $user->first_name ?? null,
                                    'last_name' => $user->last_name ?? null,
                                    'mobile' => $user->mobile ?? null,
                                    'avatar' => $user->avatar ?? url('/default/avatar.png'),
                                    'gender' => $user->gender ?? null,
                                    'city' => null,
                                ],
                                'body_areas' => [],
                                'assistant' => null,
                                'for_self' => $appointment->for_self,
                                'start_time' => [
                                    'timestamp' => strtotime("{$appointment->date_visit} {$appointment->start_time}"),
                                    'clock_type' => verta($appointment->date_visit)->hour < 13 ? 'قبل از ظهر' : 'بعد از ظهر',
                                    'time_beauty' => $appointment->start_time,
                                    'date_beauty' => verta($appointment->date_visit)->format('Y/m/d'),
                                ],
                                'end_time' => [
                                    'timestamp' => strtotime("{$appointment->date_visit} {$appointment->end_time}"),
                                    'clock_type' => verta($appointment->date_visit)->hour < 13 ? 'قبل از ظهر' : 'بعد از ظهر',
                                    'time_beauty' => $appointment->end_time,
                                    'date_beauty' => verta($appointment->date_visit)->format('Y/m/d'),
                                ],
                                'created_at' => [
                                    'timestamp' => $appointment->created_at->timestamp,
                                    'clock_type' => verta($appointment->date_visit)->hour < 13 ? 'قبل از ظهر' : 'بعد از ظهر',
                                    'time_beauty' => $appointment->created_at->format('H:i'),
                                    'date_beauty' => verta($appointment->created_at)->format('Y/m/d'),
                                ],
                                'appointment_status' => [
                                    'title' => $appointment->status->getName(),
                                    'color' => $appointment->status->getBadgeColor(),
                                    'value' =>  $appointment->status->value,
                                ],
                                'visit_status' => [
                                    'title' => $appointment->visited_at ? 'ویزیت شده' : 'ویزیت نشده',
                                    'color' => '#ffc653',
                                    'value' => 0,
                                ],

                                'transaction' => $transaction ? [
                                    'id' => $transaction->id,
                                    'code' => $transaction->transaction_code,
                                    'status' => $transaction->status->value,
                                    'paid_by' => $transaction->paid_by->value,
                                ] : null,

                                'cost' => [
                                    'total_cost' => $transaction->total_cost ?? 0,
                                    'deposit_cost' => $transaction->detail['deposit_cost'] ?? 0,
                                    'paid_cost' => $transaction->cost ?? 0,
                                    'discount_cost' => $transaction->discount_amount ?? 0,
                                    'remaining_cost' => $transaction
                                        ? max(0, ($transaction->total_cost - $transaction->cost - $transaction->discount_amount))
                                        : 0,
                                ],

                                'discount' =>  0,
                                'time_left' => [
                                    'whole_minutes' => 0,
                                    'days' => 0,
                                    'hours' => 0,
                                    'minutes' => 0,
                                ],
                                'type' => [
                                    'title' => $appointment->kind->getName(),
                                    'value' => 'in-person'
                                ],
                                'kind' => '',
                                'can_cancel' => false,
                                'user_has_voted' => false,
                                'online_call_options' => [],
                                'code' => $appointment->code ?? null,
                                'appointment_via' => [
                                    'title' =>'',
                                    'value' => $appointment->detail['APPOINTMENT_VIA'] ?? 3
                                ],
                                'survey' => [
                                    'score' => $appointment->detail['SURVEY']['SURVEY'] ?? null,
                                    'file' => isset($appointment->detail['SURVEY']['SURVEY_FEEDBACK_FILE']) && $appointment->detail['SURVEY']['SURVEY_FEEDBACK_FILE'] != ''
                                        ? url('uploads/voip/'.$appointment->detail['SURVEY']['SURVEY_FEEDBACK_FILE'])
                                        : null
                                ],
                            ];
                        }

                        $doctorItem['parts'][] = $part;
                    }

                    $office['doctors'][] = $doctorItem;
                }

                $result['offices'][] = $office;
            }
        });

        // اگر می‌خواهید فایل دانلود شود:
        $filename = 'appointments.json';
        $json = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
