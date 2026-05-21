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
        Schema::create('sermons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('speaker_name')->nullable();
            $table->date('sermon_date')->nullable();

            $table->enum('status', ['Pending', 'Scheduled', 'Completed', 'Rejected'])->default('Pending');

            $table->text('notes')->nullable();
            $table->foreignId('mosque_manager_id')->constrained('users');
            $table->foreignId('region_manager_id')->nullable()->constrained('users');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
