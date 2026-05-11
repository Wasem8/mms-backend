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
        Schema::create('mosques', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('working_hours')->nullable();
            $table->enum('status', ['active', 'maintenance','closed'])->default('active');
            $table->boolean('is_featured')->default(false);
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
          // $table->string('place_id')->nullable();
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->integer('reviews_count')->default(0);;
            $table->string('imam')->nullable();
            $table->string('khatib')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();

            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');


            $table->index(['latitude', 'longitude']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mosques');
    }
};
