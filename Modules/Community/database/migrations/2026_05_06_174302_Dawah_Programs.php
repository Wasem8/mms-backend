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
        Schema::create('dawah_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mosque_id')->constrained('mosques')->onDelete('cascade');
            $table->foreignId('space_id')->constrained('mosque_spaces')->onDelete('cascade');
            $table->string('program_name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('presenter');
            $table->time('start_time');
            $table->time('end_time');
            $table->date('date');
            $table->enum('level',['beginner','intermediate','advanced'])->default('beginner');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
