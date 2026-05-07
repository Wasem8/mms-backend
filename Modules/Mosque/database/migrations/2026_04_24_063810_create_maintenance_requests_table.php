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
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
             $table->foreignId('mosque_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->string('type')->nullable();
            
            $table->string('title');
            $table->text('description');

            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'in_progress',
                'completed'
            ])->default('pending');

            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->text('admin_notes')->nullable();

            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
