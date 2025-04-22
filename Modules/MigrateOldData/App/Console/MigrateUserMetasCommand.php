<?php

namespace Modules\MigrateOldData\App\Console;

use Illuminate\Console\Command;
use Modules\User\Entities\User;
use Illuminate\Support\Facades\DB;
use Modules\User\Enum\UserMetaEnum;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MigrateUserMetasCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'migrateData:userMetas';

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

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Connect to the old database
        $oldData = DB::connection('old_mysql')->table('user_metas')->get();
        // Loop through each record and transform it
        foreach ($oldData as $data) {
            // Transform the data according to new structure
            $newKey = $this->findMetaKeyEnumValue($data->meta_key, $data->user_id);
            $metavalue = $data->meta_value;
            if ($newKey == UserMetaEnum::AVATAR) {
                $metavalue = url('public/avatar/' . $data->meta_value);
            }
            if ($newKey != null &&  $this->checkUserForegnKey($data->user_id)) {
                $newData = [
                    'meta_key' => $newKey,
                    'user_id' => $data->user_id,
                    'meta_value' => $metavalue,
                ];
                DB::connection('new_mysql')->table('user_metas')->insert($newData);
            }
        }
        $this->info('users meta  migration completed successfully.');
    }
    private function findMetaKeyEnumValue($metaValue, $userid)
    {
        return  UserMetaEnum::fromOldKey($metaValue, $userid);
    }
    private function checkUserForegnKey($user_id)
    {
        $user_exists = User::find($user_id) !== null;
        return $user_exists;
    }
}
