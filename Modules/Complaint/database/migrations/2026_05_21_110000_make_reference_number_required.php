<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill any missing reference numbers using MR-{id} format
        $rows = DB::table('maintenance_requests')
            ->whereNull('reference_number')
            ->orWhere('reference_number', '')
            ->select('id')
            ->get();

        foreach ($rows as $row) {
            DB::table('maintenance_requests')
                ->where('id', $row->id)
                ->update(['reference_number' => sprintf('MR-%06d', $row->id)]);
        }

        // Make the column NOT NULL at the database level (Postgres)
        DB::statement('ALTER TABLE maintenance_requests ALTER COLUMN reference_number SET NOT NULL');
    }

    public function down(): void
    {
        // Revert NOT NULL constraint
        DB::statement('ALTER TABLE maintenance_requests ALTER COLUMN reference_number DROP NOT NULL');
    }
};
