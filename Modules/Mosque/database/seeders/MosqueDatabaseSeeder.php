<?php

namespace Modules\Mosque\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Mosque\Models\Mosque; // تأكد من مسار الموديل الصحيح في موديولك

class MosqueDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // مساجد دمشق الحقيقية (عربي/سوري) مع مرافقها ومساحاتها ومهامها
        $this->call([
            MosqueSeeder::class,
            MosqueFacilitySeeder::class,
            MosqueSpaceRealSeeder::class,
            MosqueTaskSeeder::class,
        ]);
    }
}
