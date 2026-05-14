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
        Schema::create('donations', function (Blueprint $table) {
            $table->id();

            $table->string('reference')->unique();

            $table->foreignId('mosque_id')->constrained()->cascadeOnDelete();

            $table->foreignId('mosque_need_id')->nullable()->constrained('mosque_needs')->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            $table->enum('type', ['cash','kind'])->default('cash');
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('item_description')->nullable();
            $table->string('donor_name')->nullable()->default('فاعل خير');
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->softDeletes();

            $table->index(['type', 'status']);
            $table->index('reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
