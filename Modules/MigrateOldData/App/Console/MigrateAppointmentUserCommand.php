<?php

namespace Modules\MigrateOldData\App\Console;

use Illuminate\Console\Command;
use Modules\User\Entities\User;
use Illuminate\Support\Facades\DB;
use Hekmatinasser\Verta\Facades\Verta;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

class MigrateAppointmentUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'migrateData:appointment_user';

    /**
     * The console command description.
     */
    protected $description = 'transer user appointment .';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Connect to the old database
        $oldData = DB::connection('old_mysql')->table('appointment_users')->get();

        // Loop through each record and transform it
        foreach ($oldData as $data) {
            // Transform the data according to new structure
            if ($this->checkUserForegnKey($data->user_id)) {
                $newData = [
                    'id' => $data->id,
                    'agent_id' => $data->agent_id,
                    'appointment_setting_id' => $this->settingId($data),
                    'user_id' => $data->user_id,
                    'doctor_id' => $data->doctor_id,
                    'service_id' => $data->appointment_part_id,
                    'place_id' => $data->appointment_office_id,
                    'operator_id' => $this->findOperatorId($data->operator),
                    'tracking_code' => $this->trackingCode($data->code),
                    'kind' => AppointmentUserKindEnum::OldData($data->type),
                    'start_time' => $data->time_from,
                    'end_time' => $data->time_to,
                    'date_visit' => $this->caculateDateVisit($data),
                    'visited_at' => $data->visit_at,
                    'details' => $this->convertDetails(),
                ];
                // Insert the transformed data into the new database
                DB::connection('mysql')->table('appointment_users')->insert($newData);
            }
        }
        $this->info('appointment user transfered successfuly.');
    }
    private function findOperatorId($oprator)
    {
        $arrop = json_decode($oprator, true);
        if (isset($arrop[0]) && $arrop[0] != null) {
            $user = User::find((int)$arrop[0]);
            if (isset($user)) {
                return $user->id;
            }
        }
        return null;
    }
    private function trackingCode($code)
    {
        if (!AppointmentUser::where('tracking_code', $code)->exists()) {
            return $code;
        } else {
            return  AppointmentUser::generateTrackingCode();
        }
    }
    private function caculateDateVisit($data)
    {
        $year = $data->year;
        $month = $data->month;
        $day = $data->day;
        $date =  Verta::parse($year . '-' . $month . '-' . $day)->tocarbon();
        return $date->TodateString();
    }
    public function convertDetails()
    {
        $detail = [
            AppointmentUser::STORE_FROM_APPLICATION => false,
            AppointmentUser::DETAIL_PAYMENT => ['status' => false],

        ];
        return json_encode($detail);
    }
    public function settingId($data)
    {
        $serviceId = $data->appointment_part_id;
        $placeId = $data->appointment_office_id;
        $setting = AppointmentSetting::where('service_id', $serviceId)->where('place_id', $placeId)->first()?->id ?? null;
        return $setting;
    }
    private function checkUserForegnKey($user_id)
    {
        $user_exists = User::find($user_id) !== null;
        return $user_exists;
    }
}
