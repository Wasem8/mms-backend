<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->string('reference_number')->nullable()->unique()->after('id');
            $table->enum('urgency', ['low', 'medium', 'high', 'urgent'])->default('low')->after('category');
        });

        // Backfill urgency from existing boolean is_urgent (true => urgent, false => low)
        if (Schema::hasColumn('maintenance_requests', 'is_urgent')) {
            DB::table('maintenance_requests')->where('is_urgent', true)->update(['urgency' => 'urgent']);
            DB::table('maintenance_requests')->whereNull('urgency')->orWhere('urgency', '')->update(['urgency' => 'low']);

            // Drop the old boolean column
            Schema::table('maintenance_requests', function (Blueprint $table) {
                $table->dropColumn('is_urgent');
            });
        }

        // Generate reference numbers for existing rows
        $rows = DB::table('maintenance_requests')->select('id')->get();
        foreach ($rows as $row) {
            $ref = sprintf('MR-%06d', $row->id);
            DB::table('maintenance_requests')->where('id', $row->id)->update(['reference_number' => $ref]);
        }
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            if (Schema::hasColumn('maintenance_requests', 'reference_number')) {
                $table->dropColumn('reference_number');
            }
            if (Schema::hasColumn('maintenance_requests', 'urgency')) {
                $table->dropColumn('urgency');
            }
            // recreate boolean is_urgent (default false)
            if (! Schema::hasColumn('maintenance_requests', 'is_urgent')) {
                $table->boolean('is_urgent')->default(false)->after('category');
            }
        });
    }
};
