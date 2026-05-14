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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['pending', 'in_progress', 'resolved','canceled'])->default('pending');
            $table->enum('priority',['low','medium','high'])->default('medium');
            $table->enum('complaint_type',['service_missing','power_outage','corruption','employee_misconduct','technical_issue']);
            $table->string('email')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->string('admin_notes')->nullable();



            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');



            $table->unsignedBigInteger('mosque_id');
            $table->foreign('mosque_id')->references('id')->on('mosques');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
