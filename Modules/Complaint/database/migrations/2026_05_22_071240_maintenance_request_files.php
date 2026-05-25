<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('maintenance_request_files', function (Blueprint $table) {
            $table->id();

            $table->foreignId('maintenance_request_id')
                ->constrained('maintenance_requests')
                ->cascadeOnDelete();

            $table->string('file');       // public Supabase URL
            $table->string('file_type'); // MIME type

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
