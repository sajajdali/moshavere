<?php

namespace Modules\Front\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Front\app\Models\Province;
use Illuminate\Contracts\Filesystem\FileNotFoundException;


class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        try {
            $provinces = json_decode(\Illuminate\Support\Facades\Storage::disk('seed')->get('provinces'));
            foreach ($provinces as $province) {
                Province::create([
                    'id'        =>  $province->id,
                    'title'     =>  $province->title,
                    'parent_id' =>  $province->parent_id,
                ]);
            }
        } catch (FileNotFoundException $e) {
            return $e->getMessage();
        }
    }
}
