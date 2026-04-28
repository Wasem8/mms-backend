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

            $table->decimal('target_amount', 10, 2)->nullable();
            $table->decimal('collected_amount', 10, 2)->default(0);

            $table->boolean('is_active')->default(true);
            $table->date('deadline')->nullable();

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
