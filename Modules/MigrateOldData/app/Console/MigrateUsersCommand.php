<?php

namespace Modules\MigrateOldData\App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MigrateUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'migrateData:user';

    /**
     * The console command description.
     */
    protected $description = 'transfer users data.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Migrate users
        $this->migrateUsers();

        // Migrate Roles
        $this->migrateUserRoles();
    }
    private function migrateUsers()
    {
        // Connect to the old database
        $oldData = DB::connection('old_mysql')->table('appointment_users')->get();
        // Loop through each record and transform it
        foreach ($oldData as $data) {
            // Transform the data according to new structure
            $newData = [
                'mobile' => $data->mobile ?? '',
                'email' => $data->email ?? '',
                'password' => $data->password ?? '',
                'remember_token' => $data->remember_token ?? '',
                // Add more transformations as needed
            ];
            DB::connection('mysql')->table('users')->insert($newData);
        }
        $this->info('users migration completed successfully.');
    }

    private function migrateUserRoles()
    {
        $oldUserRoles = DB::connection('old_mysql')->table('model_has_roles')->get();
        $this->info("Fetched " . $oldUserRoles->count() . " user roles from old database.");

        foreach ($oldUserRoles as $userRole) {
            $role = match($userRole->role_id) {
                6 => 2 ,
                2 => 3 ,
                5 => 7 ,
                4 => 5 ,
                default => 2 ,
            };
            if($userRole->model_id == 1) {
                continue ;
            }
            $newUserRole = [
                'role_id' => $role,
                'model_type' => 'Modules\User\Entities\User',
                'model_id' => $userRole->model_id,
            ];
            DB::connection('mysql')->table('model_has_roles')->insert($newUserRole);
            $this->info("roled has been assigned");
        }
    }
}
