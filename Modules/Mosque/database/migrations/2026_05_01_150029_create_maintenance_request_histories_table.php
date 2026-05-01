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
        Schema::create('maintenance_request_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_request_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('old_status', [
                'pending',
                'approved',
                'rejected',
                'in_progress',
                'completed'
            ])->nullable();

            $table->enum('new_status', [
                'pending',
                'approved',
                'rejected',
                'in_progress',
                'completed'
            ]);
            $table->foreignId('changed_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_request_histories');
    }
};
