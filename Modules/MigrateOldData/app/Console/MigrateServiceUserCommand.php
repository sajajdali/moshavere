<?php

namespace Modules\MigrateOldData\App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MigrateServiceUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'migrateData:serviceUserpivot';

    /**
     * The console command description.
     */
    protected $description = 'Command description.';

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
       $oldData = DB::connection('old_mysql')->table('appointment_part_user')->get();

       // Loop through each record and transform it
       foreach ($oldData as $data) {
           // Transform the data according to new structure
           $newData = [
               'service_id' => $data->appointment_part_id,
               'user_id' => $data->user_id,
           ];
           // Insert the transformed data into the new database
           DB::connection('mysql')->table('service_user')->insert($newData);
       }

       $this->info('service_user migration completed successfully.');
    }
}
