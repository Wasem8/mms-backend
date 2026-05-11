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
        Schema::create('program_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dawah_program_id')->constrained('dawah_programs')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
