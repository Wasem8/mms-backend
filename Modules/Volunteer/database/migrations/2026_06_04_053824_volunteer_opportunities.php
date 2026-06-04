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
        Schema::create('volunteer_opportunities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mosque_id')->index();
            $table->string('title');
            $table->text('description');
            $table->unsignedInteger('required_volunteers')->default(1);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('open'); // OpportunityStatus enum
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('mosque_id')->references('id')->on('mosques')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
