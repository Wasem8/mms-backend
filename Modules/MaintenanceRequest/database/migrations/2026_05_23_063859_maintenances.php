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
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('maintenance_number')->unique();
            $table->foreignId('mosque_id')->constrained('mosques')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('category', ['electrical', 'plumbing', 'carpentry', 'cleaning', 'other']);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
