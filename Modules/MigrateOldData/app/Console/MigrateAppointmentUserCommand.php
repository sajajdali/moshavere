<?php

namespace Modules\MigrateOldData\App\Console;

use Illuminate\Console\Command;
use Modules\User\Entities\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Modules\AppointmentUser\app\Models\AppointmentUser;

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
            $newData = [
                'agent_id' => $data->agent_id,
                'user_id' => $data->user_id,
                'doctor_id' => $data->doctor_id,
                'service_id' => $data->appointment_part_id,
                'place_id' => $data->appointment_office_id,
                'operator_id' => $this->findOperatorId($data->operator),
                'tracking_code' => $this->trackingCode($data->code),
            ];

            // Insert the transformed data into the new database
            DB::connection('mysql')->table('appointment_users')->insert($newData);
        }

        $this->info('appointment user transfered successfuly.');
    }
    private function findOperatorId($oprator)
    {
        $arrop = json_decode($oprator, true);
        if ($arrop[0] != null) {
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
}
