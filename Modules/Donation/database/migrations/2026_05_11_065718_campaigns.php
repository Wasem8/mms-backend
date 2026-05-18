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
        Schema::create(
            'campaigns',
            function (Blueprint $table) {
                $table->id();
                $table->foreignId('mosque_id')->constrained()->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->decimal('target_amount', 12, 2);
                $table->decimal('collected_amount', 12, 2)->default(0);
                $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])->default('active');
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
                $table->string('cover_image')->nullable();
                $table->timestamps();
                    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
