<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Education\Database\Seeders\EducationDatabaseSeeder;
use Modules\Mosque\Database\Seeders\FacilitiesSeeder;
use Modules\Mosque\Database\Seeders\MosqueSeeder;
use Modules\Mosque\Database\Seeders\MosqueDatabaseSeeder;
use Modules\User\Database\Seeders\RolesAndPermissionsSeeder;
use Modules\User\Models\User;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MosqueDatabaseSeeder::class,
            RolesAndPermissionsSeeder::class,
            EducationDatabaseSeeder::class,
            MosqueSeeder::class,
            FacilitiesSeeder::class

        ]);


    }
}
