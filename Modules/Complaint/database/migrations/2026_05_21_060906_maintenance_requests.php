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

        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mosque_id')
                ->constrained('mosques')
                ->cascadeOnDelete();

            $table->foreignId('region_manager_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title');
            $table->text('description');

            $table->enum('category', [
                'hvac',
                'electrical',
                'plumbing',
                'sound_system',
                'general',
            ]);

            $table->boolean('is_urgent')->default(false);

            $table->enum('status', [
                'pending',
                'in_progress',
                'completed',
                'rejected',
            ])->default('pending');

            $table->text('rejection_reason')->nullable();
            $table->json('attachments')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
