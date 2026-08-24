<?php

namespace Modules\Volunteer\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Volunteer\Database\Seeders\VolunteerSeeder;

class VolunteerDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(VolunteerSeeder::class);
    }
}
