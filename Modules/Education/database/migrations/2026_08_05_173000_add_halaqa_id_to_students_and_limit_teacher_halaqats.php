<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('halaqa_id')
                ->nullable()
                ->after('mosque_id')
                ->constrained('halaqats')
                ->nullOnDelete();
        });

        $studentHalaqat = DB::table('halaqa_student')
            ->orderBy('student_id')
            ->orderByRaw('joined_at is null')
            ->orderBy('joined_at')
            ->orderBy('id')
            ->get(['student_id', 'halaqa_id'])
            ->groupBy('student_id');

        foreach ($studentHalaqat as $studentId => $rows) {
            $halaqaId = $rows->first()->halaqa_id;

            DB::table('students')
                ->where('id', $studentId)
                ->update(['halaqa_id' => $halaqaId]);
        }

        $duplicateTeacherGroups = DB::table('halaqats')
            ->whereNotNull('teacher_id')
            ->orderBy('teacher_id')
            ->orderBy('id')
            ->get(['id', 'teacher_id'])
            ->groupBy('teacher_id');

        foreach ($duplicateTeacherGroups as $teacherId => $rows) {
            $rows->skip(1)->each(function ($row) {
                DB::table('halaqats')
                    ->where('id', $row->id)
                    ->update(['teacher_id' => null]);
            });
        }

        Schema::table('halaqats', function (Blueprint $table) {
            $table->unique('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::table('halaqats', function (Blueprint $table) {
            $table->dropUnique(['teacher_id']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('halaqa_id');
        });
    }
};
