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
        Schema::create('mosque_needs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mosque_id')
                ->constrained('mosques')
                ->cascadeOnDelete();

            $table->string('title');
            $table->text('description');

            $table->enum('type', [
                'financial',
                'maintenance',
                'equipment',
                'supplies',
                'other'
            ])->default('other');
            $table->string('image')->nullable();

            $table->decimal('target_amount', 10, 2)->nullable();
            $table->decimal('collected_amount', 10, 2)->default(0);
            $table->enum('status', [
                'open',
                'partially_fulfilled',
                'fulfilled'
            ])->default('open');
            $table->boolean('is_urgent')->default(true);
            

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mosque_needs');
    }
};
