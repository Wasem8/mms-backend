<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mosques', function (Blueprint $table) {
            $table->foreignId('city_id')
                ->nullable()
                ->after('district')
                ->constrained('cities')
                ->nullOnDelete();

            $table->foreignId('district_id')
                ->nullable()
                ->after('city_id')
                ->constrained('districts')
                ->nullOnDelete();

            $table->index(['city_id', 'district_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mosques', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropForeign(['district_id']);
            $table->dropColumn(['city_id', 'district_id']);
        });
    }
};
