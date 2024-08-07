<?php

namespace Modules\MigrateOldData\App\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MigrateAllOrders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'migrateData:all';

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
          // List of commands to run
          $commands = [
            'migrateData:user',
            'migrateData:userMetas',
            'migrateData:place',
            'migrateData:PlaceUserPivot',
            'migrateData:service',
            'migrateData:serviceUserpivot',
            'migrateData:specility',
            'migrateData:appointment_setting',
            'migrateData:appointment_user',
        ];
        // Run each command
        try{
            foreach ($commands as $command) {
                $this->call($command);
            }
        }catch(\Exception $e){
            dd($e->getMessage());
        }
        $this->info('Data migration completed successfully.');
    }

}
