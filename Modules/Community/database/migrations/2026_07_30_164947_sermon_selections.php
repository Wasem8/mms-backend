<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sermon_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mosque_manager_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sermon_id')->constrained('sermons')->cascadeOnDelete();
            $table->date('friday_date');
            $table->timestamps();

            $table->unique(['mosque_manager_id', 'friday_date'], 'sermon_selections_manager_date_unique');
            $table->index('friday_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sermon_selections');
    }
};
