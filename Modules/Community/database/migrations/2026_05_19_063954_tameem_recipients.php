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
        Schema::create('tameem_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tameem_id')
                ->constrained('tameems')
                ->cascadeOnDelete();
            $table->foreignId('mosque_manager_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['tameem_id', 'mosque_manager_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
