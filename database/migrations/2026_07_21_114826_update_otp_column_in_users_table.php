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
        Schema::table('users', function (Blueprint $table) {
            // توسيع الحجم إلى 255 حرفاً لاستيعاب النص المشفّر (Hash)
            $table->string('otp', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // إعادة الحجم إلى 6 أحرف في حال إلغاء التعديل
            $table->string('otp', 6)->nullable()->change();
        });
    }
};
