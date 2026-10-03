<?php

namespace Modules\MigrateOldData\App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
            'migrateData:appointment_transactions',
        ];
        $previousDefaultConnection = DB::getDefaultConnection();

        try {
            DB::setDefaultConnection('new_mysql');

            DB::connection('new_mysql')->transaction(function () use ($commands) {
                foreach ($commands as $command) {
                    $exitCode = $this->call($command);

                    if ($exitCode !== self::SUCCESS) {
                        throw new \RuntimeException("Migration command failed: {$command}");
                    }
                }
            });
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        } finally {
            DB::setDefaultConnection($previousDefaultConnection);
        }

        $this->info('Data migration completed successfully.');

        return self::SUCCESS;
    }

}
