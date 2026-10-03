<?php

namespace Modules\MigrateOldData\App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\User\Enum\UserMetaEnum;
use Modules\User\Enum\UserSpecialityType;

class MigrateSpecialiteiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'migrateData:specility';

    /**
     * The console command description.
     */
    protected $description = 'transfer specialities data.';

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
        $oldData = DB::connection('old_mysql')->table('appointment_specialties')->get();
        $priority = (int) (DB::connection('new_mysql')->table('specialities')->max('priority') ?? 0);
        // Loop through each record and transform it
        foreach ($oldData as $data) {
            // Transform the data according to new structure
            $existingPriority = DB::connection('new_mysql')
                ->table('specialities')
                ->where('id', $data->id)
                ->value('priority');
            $priority++;
            $newData = [
                'title' => $data->name,
                'priority' => $existingPriority ?? $priority,
                'active' => \Modules\Speciality\Enum\SpecialityStatusEnum::ACTIVE,
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at,
            ];
            DB::connection('new_mysql')->table('specialities')->updateOrInsert(
                ['id' => $data->id],
                $newData,
            );
        }

        $assignments = DB::connection('old_mysql')
            ->table('user_metas')
            ->where('meta_key', 'SPECIALTY')
            ->get();
        $doctorIds = [];
        $assignmentCount = 0;

        foreach ($assignments as $assignment) {
            $userId = (int) $assignment->user_id;
            $specialityId = (int) $assignment->meta_value;

            if (! DB::connection('new_mysql')->table('users')->where('id', $userId)->exists()
                || ! DB::connection('new_mysql')->table('specialities')->where('id', $specialityId)->exists()) {
                continue;
            }

            DB::connection('new_mysql')->table('speciality_user')->updateOrInsert(
                ['user_id' => $userId, 'speciality_id' => $specialityId],
                ['updated_at' => now(), 'created_at' => now()],
            );
            $doctorIds[$userId] = true;
            $assignmentCount++;
        }

        foreach (array_keys($doctorIds) as $doctorId) {
            DB::connection('new_mysql')->table('user_metas')
                ->where('user_id', $doctorId)
                ->where('meta_key', UserMetaEnum::SPECIALITY_TYPE->value)
                ->delete();
            DB::connection('new_mysql')->table('user_metas')->insert([
                'user_id' => $doctorId,
                'meta_key' => UserMetaEnum::SPECIALITY_TYPE->value,
                'meta_value' => UserSpecialityType::DOCTOR->value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->info($oldData->count() . ' specialities and ' . $assignmentCount . ' doctor assignments migrated successfully.');
    }
}
