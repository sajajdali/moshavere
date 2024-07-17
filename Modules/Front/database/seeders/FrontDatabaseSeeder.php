<?php

namespace Modules\Front\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Front\database\seeders\ProvinceSeeder;

class FrontDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        dd('test');
        $this->call(ProvinceSeeder::class);
    }
}
