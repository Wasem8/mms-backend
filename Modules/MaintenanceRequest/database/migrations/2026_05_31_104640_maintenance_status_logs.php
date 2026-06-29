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

        Schema::create('maintenance_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_id')->constrained('maintenances')->cascadeOnDelete();
            $table->enum('old_status', ['pending', 'in_progress', 'completed', 'cancelled'])->nullable()
                ->comment('null on first log entry');
            $table->enum('new_status', ['pending', 'in_progress', 'completed', 'cancelled']);
            $table->string('changed_by')->comment('name or user identifier who made the change');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
