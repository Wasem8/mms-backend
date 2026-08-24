<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add opportunity_id, nullable for now so we can backfill existing rows.
        Schema::table('volunteer_tasks', function (Blueprint $table) {
            $table->foreignId('opportunity_id')
                ->nullable()
                ->after('id')
                ->constrained('volunteer_opportunities')
                ->cascadeOnDelete();
        });

        // 2. Backfill opportunity_id for existing tasks from their application's opportunity.
        // Native Postgres UPDATE...FROM syntax — Laravel's join()->update() on Postgres
        // uses a ctid-based subquery that can't reference the joined table's columns in SET.
        DB::statement('
            UPDATE volunteer_tasks
            SET opportunity_id = volunteer_applications.opportunity_id
            FROM volunteer_applications
            WHERE volunteer_tasks.application_id = volunteer_applications.id
        ');

        // 3. application_id becomes optional — a task can exist unassigned until a manager distributes it.
        Schema::table('volunteer_tasks', function (Blueprint $table) {
            $table->dropForeign(['application_id']);
        });

        DB::statement('ALTER TABLE volunteer_tasks ALTER COLUMN application_id DROP NOT NULL');

        Schema::table('volunteer_tasks', function (Blueprint $table) {
            // nullOnDelete (not cascade): if the application is removed, the task
            // should fall back to "unassigned" on its opportunity, not disappear.
            $table->foreign('application_id')
                ->references('id')->on('volunteer_applications')
                ->nullOnDelete();
        });

        // 4. Every row is backfilled now — enforce opportunity_id as required going forward.
        DB::statement('ALTER TABLE volunteer_tasks ALTER COLUMN opportunity_id SET NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('volunteer_tasks', function (Blueprint $table) {
            $table->dropForeign(['application_id']);
        });

        DB::statement('ALTER TABLE volunteer_tasks ALTER COLUMN application_id SET NOT NULL');

        Schema::table('volunteer_tasks', function (Blueprint $table) {
            $table->foreign('application_id')
                ->references('id')->on('volunteer_applications')
                ->cascadeOnDelete();

            $table->dropForeign(['opportunity_id']);
            $table->dropColumn('opportunity_id');
        });
    }
};
