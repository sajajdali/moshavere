<?php

namespace Modules\MigrateOldData\App\Console;

use App\Enum\ActiveEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Service\Enum\ServiceShowTypeEnum;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MigrateServiceseCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'migrateData:service';

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
        $oldData = DB::connection('old_mysql')->table('appointment_parts')->orderBy('id')->get();

        // Loop through each record and transform it
        foreach ($oldData as $data) {
            // Transform the data according to new structure
            $newData = [
                'id' => $data->id,
                'title' => $data->title,
                'parent_id' => $data->parent_id == 0 ? null : ($data->parent_id),
                'show_type' => ServiceShowTypeEnum::tryFrom($data->show_type),
                'active' => ActiveEnum::tryFrom($data->status),
                'priority' => \Modules\Service\app\Models\Service::maxPriority(),
            ];

            // Insert the transformed data into the new database
            DB::connection('mysql')->table('services')->insert($newData);
        }

        $this->info('service migration completed successfully.');
    }

}
