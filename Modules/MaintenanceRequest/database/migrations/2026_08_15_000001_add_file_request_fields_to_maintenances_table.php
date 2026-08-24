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
        Schema::table('maintenances', function (Blueprint $table) {
            $table->boolean('files_requested')->default(false)->after('notes');
            $table->foreignId('files_requested_by')->nullable()->constrained('users')->nullOnDelete()->after('files_requested');
            $table->timestamp('files_requested_at')->nullable()->after('files_requested_by');
            $table->text('files_request_note')->nullable()->after('files_requested_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('files_requested_by');
            $table->dropColumn(['files_requested', 'files_requested_at', 'files_request_note']);
        });
    }
};
