<?php

namespace Modules\MigrateOldData\App\Console;

use App\Enum\ActiveEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Place\app\Models\Place;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MigratePlacesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'migrateData:place';

    /**
     * The console command description.
     */
    protected $description = 'import places.';

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
        $oldData = DB::connection('old_mysql')->table('appointment_offices')->get();

        // Loop through each record and transform it
        foreach ($oldData as $data) {
            // Transform the data according to new structure
            $newData = [
                'id' => $data->id,
                'title' => $data->name ?? 'بدون نام',
                'active' => $this->StatusCheck($data->status),
                'priority' => Place::maxPriority(),
                'detail' => $this->createDetails($data),
            ];

            // Insert the transformed data into the new database
            DB::connection('mysql')->table('places')->insert($newData);
        }

        $this->info($oldData->count() . ' place migration completed successfully.');
    }

    private function createDetails($oldValue)
    {
        $detail = [];
        if (isset($oldValue->phone)) {
            $numbers = json_decode($oldValue->phone, true);
            if (isset($numbers['number'])) {
                $detail[Place::DETAIL_KEY_NUMBERS] = $numbers['number'];
            }
        }
        if (isset($oldValue->address)) {
            $detail[Place::DETAIL_ADDRESS] = $oldValue->address;
        }
        if (isset($oldValue->longitude) && isset($oldValue->latitude)) {
            $detail[Place::DETAIL_KEY_LOCATION] = [
                Place::DETAIL_KEY_LOCATION_LAT => $oldValue->latitude,
                Place::DETAIL_KEY_LOCATION_LNG => $oldValue->longitude,
            ];
        }
        return json_encode($detail);
    }
    private function StatusCheck($active)
    {
        if ($active != '10') {
            return \App\Enum\ActiveEnum::DEACTIVE;
        } else {
            return \App\Enum\ActiveEnum::ACTIVE;
        }
    }
}
