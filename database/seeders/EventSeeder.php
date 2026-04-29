<?php

namespace Database\Seeders;

use App\Event;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): string
    {
        try {
            // $events = json_decode(Storage::disk('seed')->get('events.json'), true);

            // foreach ($events as $event) {
            //     Event::create($event);
            // }
            
            $sql = Storage::disk('seed')->get('events.sql');
            DB::unprepared($sql);

            return true;
        } catch (FileNotFoundException $e) {
            return $e->getMessage();
        }
    }
}
