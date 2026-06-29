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
            $table->enum('donation_type', ['cash', 'in_kind'])->default('cash');


            $table->enum('payment_method', ['cash', 'stripe'])->default('cash');
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 3)
                ->default('SYP')
                ->after('amount');
            $table->decimal('exchange_rate', 15, 4)
                ->default(1.0000)
                ->after('currency');
            $table->decimal('base_amount', 15, 2)
                ->default(0)
                ->after('exchange_rate');
            $table->string('item_description')->nullable();
            $table->string('donor_name')->nullable()->default('فاعل خير');
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->string('stripe_payment_intent_id')->nullable()->index();
            $table->timestamps();

            $table->softDeletes();
            $table->index('reference');
            $table->index(['donation_type', 'status']);
            $table->index(['campaign_id',   'status']);
            $table->index(['mosque_need_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
