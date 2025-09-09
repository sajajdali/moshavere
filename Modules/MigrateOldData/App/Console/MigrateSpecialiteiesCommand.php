<?php

namespace Modules\MigrateOldData\App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
            $priority++ ;
            $newData = [
                'id' => $data->id,
                'title' => $data->name,
                'priority' => $priority,
                'active' => \Modules\Speciality\Enum\SpecialityStatusEnum::ACTIVE,
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at,
                // Add more transformations as needed
            ];
            DB::connection('new_mysql')->table('specialities')->insert($newData);
        }

        $this->info('specialities migrate successfully.');
    }
}
