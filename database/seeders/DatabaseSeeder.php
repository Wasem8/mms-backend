<?php

namespace Database\Seeders;

use Database\Seeders\SermonSeeder as SeedersSermonSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Education\Database\Seeders\EducationDatabaseSeeder;
use Modules\Geo\Database\Seeders\GeoDatabaseSeeder;
use Modules\Mosque\Database\Seeders\FacilitiesSeeder;
use Modules\Mosque\Database\Seeders\MosqueSeeder;
use Modules\Mosque\Database\Seeders\MosqueDatabaseSeeder;
use Modules\Mosque\Database\Seeders\MosqueSpaceSeedSeeder;
use Modules\User\Database\Seeders\PermissionSeeder;
use Modules\User\Database\Seeders\RolePermissionSeeder;
use Modules\User\Database\Seeders\RolesAndPermissionsSeeder;
use Modules\User\Database\Seeders\RoleSeeder;
use Modules\User\Database\Seeders\UserSeeder;
use Modules\User\Models\User;
use Modules\Donation\Database\Seeders\SettingSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
//            MosqueDatabaseSeeder::class,
//            RolesAndPermissionsSeeder::class,
//            EducationDatabaseSeeder::class,
//            MosqueSeeder::class,
//            FacilitiesSeeder::class,
//            MosqueSpaceSeedSeeder::class,
//            SettingSeeder::class,
//            \Modules\Donation\Database\Seeders\DonationDatabaseSeeder::class,
//            \Modules\Complaint\Database\Seeders\ComplaintDatabaseSeeder::class,
//            \Modules\MaintenanceRequest\Database\Seeders\MaintenanceRequestDatabaseSeeder::class,
//            SeedersSermonSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            GeoDatabaseSeeder::class,
            mosqueSeeder::class,
            UserSeeder::class,
            EducationDatabaseSeeder::class


        ]);


    }
}
