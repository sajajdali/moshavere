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
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
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
            $existingAppointment = DB::connection('new_mysql')
                ->table('appointment_users')
                ->where('id', $data->id)
                ->first();

            if ($existingAppointment) {
                $this->repairMigratedCancellationStatus($data, $existingAppointment);
                $this->repairMigratedSettingLink($data, $existingAppointment);
                $this->repairMigratedDeletionState($data, $existingAppointment);
                continue;
            }

            // Transform the data according to new structure
            if (is_null($data->user_id) || $this->checkUserForegnKey($data->user_id)) {
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
                    'status' => $this->mapLegacyStatus((int) $data->status),
                    'kind' => AppointmentUserKindEnum::OldData($data->type),
                    'start_time' => $data->time_from,
                    'end_time' => $data->time_to,
                    'date_visit' => $this->caculateDateVisit($data),
                    'visited_at' => $data->visit_at,
                    'details' => $this->convertDetails(),
                    'deleted_at' => $data->deleted_at,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,
                ];
                // Insert the transformed data into the new database
                DB::connection('new_mysql')->table('appointment_users')->insert($newData);
            }
        }
        $this->info('appointment user transfered successfuly.');
    }

    /**
     * The legacy application stored 0=pending, 1=confirmed and 2=cancelled.
     */
    public function mapLegacyStatus(int $status): int
    {
        return match ($status) {
            0 => AppointmentUserStatusEnum::STATUS_PENDING->value,
            2 => AppointmentUserStatusEnum::STATUS_CANCEL->value,
            default => AppointmentUserStatusEnum::STATUS_SUCCESSFUL->value,
        };
    }

    private function repairMigratedCancellationStatus(object $legacy, object $current): void
    {
        // Earlier imports omitted status, so MySQL assigned the confirmed default (1).
        // Only repair that exact legacy mistake; do not overwrite later admin changes.
        if ((int) $legacy->status !== 2
            || (int) $current->status !== AppointmentUserStatusEnum::STATUS_SUCCESSFUL->value) {
            return;
        }

        DB::connection('new_mysql')
            ->table('appointment_users')
            ->where('id', $legacy->id)
            ->where('status', AppointmentUserStatusEnum::STATUS_SUCCESSFUL->value)
            ->update(['status' => AppointmentUserStatusEnum::STATUS_CANCEL->value]);
    }

    private function repairMigratedSettingLink(object $legacy, object $current): void
    {
        $currentSettingDoctorId = $current->appointment_setting_id
            ? DB::connection('new_mysql')->table('appointment_settings')
                ->where('id', $current->appointment_setting_id)
                ->value('user_id')
            : null;

        // Keep valid links (including any later manual correction) untouched.
        if ((int) $currentSettingDoctorId === (int) $legacy->doctor_id) {
            return;
        }

        DB::connection('new_mysql')
            ->table('appointment_users')
            ->where('id', $legacy->id)
            ->update(['appointment_setting_id' => $this->settingId($legacy)]);
    }

    private function repairMigratedDeletionState(object $legacy, object $current): void
    {
        // Preserve legacy soft-deletes without restoring anything deleted later in the new app.
        if ($legacy->deleted_at === null || $current->deleted_at !== null) {
            return;
        }

        DB::connection('new_mysql')
            ->table('appointment_users')
            ->where('id', $legacy->id)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => $legacy->deleted_at]);
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
            ->where('user_id', $data->doctor_id)
            ->where('service_id', $serviceId)
            ->where('place_id', $placeId)
            ->whereNull('deleted_at')
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
