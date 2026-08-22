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
        // مساجد سوريا فقط (بدون مرافق/مساحات/احتياجات/مهام حسب الطلب)
        $this->call(MosqueSeeder::class);
    }
}
