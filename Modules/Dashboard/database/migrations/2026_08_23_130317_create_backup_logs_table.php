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
        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();

            $table->string('file_name');
            $table->string('storage_path')->nullable();

            $table->unsignedBigInteger('file_size')->nullable();

            $table->string('checksum', 64)->nullable();

            $table->string('status', 20)->default('running');
            // running | completed | failed

            $table->string('triggered_by', 30)->default('manual');
            // manual | scheduled

            $table->text('error_message')->nullable();

            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('triggered_by');
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
    }
};
