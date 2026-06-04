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
        Schema::create('volunteer_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('volunteer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('opportunity_id')->constrained('volunteer_opportunities')->cascadeOnDelete();
            $table->decimal('logged_hours', 5, 2);
            $table->text('manager_evaluation');
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
