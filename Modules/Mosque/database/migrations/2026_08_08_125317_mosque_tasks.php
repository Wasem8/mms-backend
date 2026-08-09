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
        Schema::create('mosque_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mosque_id')->constrained('mosques')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->string('title');
            $table->string('category'); // prayer_worship | cleaning | maintenance | activity | administrative

            $table->date('due_date');
            $table->time('due_time')->nullable();

            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_important')->default(false);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['mosque_id', 'due_date']);
            $table->index(['mosque_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mosque_tasks');
    }
};
