<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Generalize the tameem recipient pivot so that any user (mosque manager,
     * halaqa supervisor, or teacher) can receive a tameem. The old
     * `mosque_manager_id` column only referenced mosque managers, while the new
     * `user_id` column references the `users` table for any recipient role.
     */
    public function up(): void
    {
        Schema::table('tameem_recipients', function (Blueprint $table) {
            $table->dropUnique('tameem_recipients_tameem_id_mosque_manager_id_unique');
            $table->dropForeign(['mosque_manager_id']);
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();
        });

        // Carry over existing mosque-manager recipients into the new column.
        DB::statement('UPDATE tameem_recipients SET user_id = mosque_manager_id');

        Schema::table('tameem_recipients', function (Blueprint $table) {
            $table->dropColumn('mosque_manager_id');
            $table->unique(['tameem_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('tameem_recipients', function (Blueprint $table) {
            $table->dropUnique('tameem_recipients_tameem_id_user_id_unique');
            $table->dropForeign(['user_id']);
            $table->foreignId('mosque_manager_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();
        });

        DB::statement('UPDATE tameem_recipients SET mosque_manager_id = user_id');

        Schema::table('tameem_recipients', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->unique(['tameem_id', 'mosque_manager_id']);
        });
    }
};
