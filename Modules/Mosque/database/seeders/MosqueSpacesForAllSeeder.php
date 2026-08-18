<?php

namespace Modules\Mosque\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Mosque\Models\Mosque;
use Modules\Mosque\Models\MosqueSpace;

class MosqueSpacesForAllSeeder extends Seeder
{
    /**
     * Ensure every mosque has at least one space.
     */
    public function run(): void
    {
        $mosques = Mosque::all();

        foreach ($mosques as $mosque) {
            $hasSpace = MosqueSpace::where('mosque_id', $mosque->id)->exists();

            if ($hasSpace) {
                continue;
            }

            MosqueSpace::create([
                'mosque_id' => $mosque->id,
                'name' => 'القاعة الرئيسية',
                'capacity' => 100,
            ]);

            $this->command->info("Created space for mosque #{$mosque->id}");
        }
    }
}
