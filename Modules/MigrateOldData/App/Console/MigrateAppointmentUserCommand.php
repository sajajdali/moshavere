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
    protected $description = 'transfer user appointment .';

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
                    'agent_id' => $this->agentIdExists($data->agent_id) ? $data->agent_id : null,
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
                DB::connection('new_mysql')->table('appointment_users')->insert($newData);
            }
        }
        $this->info('appointment user transfered successfuly.');
    }
    private function agentIdExists($id){
        return Db::connection('new_mysql')->table('users')->where('id',$id)->exists();
    }
    private function findOperatorId($oprator)
    {
        $ids = (array) json_decode($oprator, true);
        $first = isset($ids[0]) ? (int)$ids[0] : null;
        if (!$first) return null;

        // don’t rely on Eloquent’s default connection
        $id = DB::connection('new_mysql')
            ->table('users')  // shw_users with prefix
            ->whereKey($first)
            ->value('id');

        return $id ?: null;
    }
    private function trackingCode($code)
    {
        if (! DB::connection('new_mysql')
            ->table('appointment_users')->where('tracking_code', $code)->exists()) {
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
        $setting = DB::connection('new_mysql')
            ->table('appointment_settings')
            ->where('service_id', $serviceId)
            ->where('place_id', $placeId)
            ->first()?->id ?? null;
        return $setting;
    }
    private function checkUserForegnKey($user_id)
    {
        $user_exists = DB::connection('new_mysql')
            ->table('users')
            ->where('id', $user_id)
            ->first() !== null;
        return $user_exists;
    }
}
