<?php

namespace Modules\MigrateOldData\App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MigratePlaceUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'migrateData:PlaceUserPivot';

    /**
     * The console command description.
     */
    protected $description = 'imporort connection between place in users.';

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
         $oldData = DB::connection('old_mysql')->table('appointment_office_doctor')->get();
         // Loop through each record and transform it
         foreach ($oldData as $data) {
             // Transform the data according to new structure
             if($this->checkUserForegnKey($data->user_id)) {
                $newData = [
                    'user_id' => $data->user_id,
                    'place_id' => $data->appointment_office_id,
                ];
                // Insert the transformed data into the new database
                DB::connection('new_mysql')->table('place_user')->insert($newData);
             }
         }

         $this->info('place_user migration completed successfully.');
    }

    private function checkUserForegnKey($user_id)
    {
        $user_exists = \Modules\User\Entities\User::find($user_id) !== null;
        return $user_exists ;
    }

}
