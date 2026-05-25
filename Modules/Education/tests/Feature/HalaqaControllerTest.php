<?php

namespace Modules\Education\tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Education\Models\Halaqa;
use Modules\Education\Models\Student;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;
use Tests\TestCase;
use Modules\Education\Database\Factories\StudentFactory;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // إعداد البيانات الأساسية
    $this->mosque = Mosque::factory()->create();
    $this->supervisor = User::factory()->supervisor($this->mosque)->create();
    $this->teacher = User::factory()->teacher($this->mosque)->create();
    $this->areaManager = User::factory()->areaManager()->create();
});

// ========================
// اختبارات LIST (INDEX)
// ========================

test('supervisor can list only their mosque halaqat', function () {
    // إنشاء حلقات في مسجد الـ supervisor
    $halaqat = Halaqa::factory()
        ->forMosque($this->mosque)
        ->count(3)
        ->create();

    // إنشاء مسجد آخر وحلقات فيه
    $otherMosque = Mosque::factory()->create();
    Halaqa::factory()
        ->forMosque($otherMosque)
        ->count(2)
        ->create();

    $response = $this->actingAs($this->supervisor)
        ->getJson('/api/education/halaqat');

    $response->assertStatus(200);
    $data = $response->json('data');
    expect($data)->toHaveCount(3);

    // التحقق من أن جميع الحلقات تنتمي لنفس المسجد
    foreach ($data as $halaqa) {
        expect($halaqa['mosque']['id'])->toBe($this->mosque->id);
    }
});

test('teacher can list only their halaqat', function () {
    // إنشاء حلقات للـ teacher
    $halaqat = Halaqa::factory()
        ->forMosque($this->mosque)
        ->withTeacher($this->teacher)
        ->count(3)
        ->create();

    // إنشاء حلقات لمعلم آخر
    $otherTeacher = User::factory()->teacher($this->mosque)->create();
    Halaqa::factory()
        ->forMosque($this->mosque)
        ->withTeacher($otherTeacher)
        ->count(2)
        ->create();

    $response = $this->actingAs($this->teacher)
        ->getJson('/api/education/halaqat');

    $response->assertStatus(200);
    $data = $response->json('data');
    expect($data)->toHaveCount(3);

    // التحقق من أن جميع الحلقات للمعلم الحالي
    foreach ($data as $halaqa) {
        expect($halaqa['teacher']['id'])->toBe($this->teacher->id);
    }
});

test('area manager can list all halaqat', function () {
    Halaqa::factory()->forMosque($this->mosque)->count(3)->create();

    $otherMosque = Mosque::factory()->create();
    Halaqa::factory()->forMosque($otherMosque)->count(2)->create();

    $response = $this->actingAs($this->areaManager)
        ->getJson('/api/education/halaqat');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(5);
});

test('list includes pagination', function () {
    Halaqa::factory()->forMosque($this->mosque)->count(15)->create();

    $response = $this->actingAs($this->supervisor)
        ->getJson('/api/education/halaqat');

    $response->assertStatus(200);
    $pagination = $response->json('meta');

    expect($pagination['per_page'])->toBe(10);
    expect($pagination['total'])->toBe(15);
    expect($response->json('data'))->toHaveCount(10);
});

// ========================
// اختبارات CREATE (STORE)
// ========================

test('supervisor can create halaqa', function () {
    $data = [
        'name' => 'حلقة القرآن الكريم',
        'teacher_id' => $this->teacher->id,
        'capacity' => 30,
        'schedule_days' => ['sunday', 'tuesday', 'thursday'],
        'start_time' => '09:00:00',
        'end_time' => '10:00:00',
        'status' => 'active',
    ];

    $response = $this->actingAs($this->supervisor)
        ->postJson('/api/education/halaqat', $data);

    $response->assertStatus(200);

    $this->assertDatabaseHas('halaqats', [
        'name' => 'حلقة القرآن الكريم',
        'teacher_id' => $this->teacher->id,
        'mosque_id' => $this->supervisor->mosque_id,
        'capacity' => 30,
    ]);
});

test('supervisor without mosque cannot create halaqa', function () {
    $supervisorWithoutMosque = User::factory()
        ->state(['mosque_id' => null])
        ->create();
    $supervisorWithoutMosque->assignRole('halaqa_supervisor');

    $data = [
        'name' => 'حلقة جديدة',
        'capacity' => 30,
        'start_time' => '09:00:00',
        'end_time' => '10:00:00',
    ];

    $response = $this->actingAs($supervisorWithoutMosque)
        ->postJson('/api/education/halaqat', $data);

    $response->assertStatus(500);
});

test('validation fails with missing required fields', function () {
    $response = $this->actingAs($this->supervisor)
        ->postJson('/api/education/halaqat', [
            'name' => 'حلقة بدون بيانات أخرى',
        ]);

    $response->assertStatus(422);
    $response->assertInvalid(['capacity', 'start_time', 'end_time']);
});

test('validation fails with invalid time format', function () {
    $data = [
        'name' => 'حلقة',
        'capacity' => 30,
        'start_time' => 'invalid-time',
        'end_time' => '10:00:00',
    ];

    $response = $this->actingAs($this->supervisor)
        ->postJson('/api/education/halaqat', $data);

    $response->assertStatus(422);
    $response->assertInvalid('start_time');
});

test('validation fails when end time is before start time', function () {
    $data = [
        'name' => 'حلقة',
        'capacity' => 30,
        'schedule_days' => ['sunday'],
        'start_time' => '10:00:00',
        'end_time' => '09:00:00',
    ];

    $response = $this->actingAs($this->supervisor)
        ->postJson('/api/education/halaqat', $data);

    $response->assertStatus(422);
    $response->assertInvalid('end_time');
});

test('validation fails with invalid teacher', function () {
    $data = [
        'name' => 'حلقة',
        'teacher_id' => 999,
        'capacity' => 30,
        'start_time' => '09:00:00',
        'end_time' => '10:00:00',
    ];

    $response = $this->actingAs($this->supervisor)
        ->postJson('/api/education/halaqat', $data);

    $response->assertStatus(422);
    $response->assertInvalid('teacher_id');
});

test('validation fails when teacher is not active', function () {
    $inactiveTeacher = User::factory()
        ->teacher($this->mosque)
        ->inactive()
        ->create();

    $data = [
        'name' => 'حلقة',
        'teacher_id' => $inactiveTeacher->id,
        'capacity' => 30,
        'start_time' => '09:00:00',
        'end_time' => '10:00:00',
    ];

    $response = $this->actingAs($this->supervisor)
        ->postJson('/api/education/halaqat', $data);

    $response->assertStatus(422);
    $response->assertInvalid('teacher_id');
});

// ========================
// اختبارات SHOW
// ========================

test('supervisor can view their mosque halaqa', function () {
    $halaqa = Halaqa::factory()
        ->forMosque($this->mosque)
        ->withTeacher($this->teacher)
        ->create();

    $response = $this->actingAs($this->supervisor)
        ->getJson("/api/education/halaqat/{$halaqa->id}");

    $response->assertStatus(200);
    $data = $response->json('data');

    expect($data['id'])->toBe($halaqa->id);
    expect($data['name'])->toBe($halaqa->name);
    expect($data['mosque']['id'])->toBe($this->mosque->id);
});

test('teacher can view their halaqa', function () {
    $halaqa = Halaqa::factory()
        ->forMosque($this->mosque)
        ->withTeacher($this->teacher)
        ->create();

    $response = $this->actingAs($this->teacher)
        ->getJson("/api/education/halaqat/{$halaqa->id}");

    $response->assertStatus(200);
});

test('supervisor cannot view other mosque halaqa', function () {
    $otherMosque = Mosque::factory()->create();
    $halaqa = Halaqa::factory()
        ->forMosque($otherMosque)
        ->create();

    $response = $this->actingAs($this->supervisor)
        ->getJson("/api/education/halaqat/{$halaqa->id}");

    $response->assertStatus(404);
});

test('teacher cannot view other teachers halaqa', function () {
    $otherTeacher = User::factory()->teacher($this->mosque)->create();
    $halaqa = Halaqa::factory()
        ->forMosque($this->mosque)
        ->withTeacher($otherTeacher)
        ->create();

    $response = $this->actingAs($this->teacher)
        ->getJson("/api/education/halaqat/{$halaqa->id}");

    $response->assertStatus(404);
});

// ========================
// اختبارات UPDATE
// ========================

test('supervisor can update their mosque halaqa', function () {
    $halaqa = Halaqa::factory()
        ->forMosque($this->mosque)
        ->create();

    $data = [
        'name' => 'الاسم الجديد للحلقة',
        'capacity' => 40,
    ];

    $response = $this->actingAs($this->supervisor)
        ->putJson("/api/education/halaqat/{$halaqa->id}", $data);

    $response->assertStatus(200);

    $this->assertDatabaseHas('halaqats', [
        'id' => $halaqa->id,
        'name' => 'الاسم الجديد للحلقة',
        'capacity' => 40,
    ]);
});

test('teacher can update their halaqa', function () {
    $halaqa = Halaqa::factory()
        ->forMosque($this->mosque)
        ->withTeacher($this->teacher)
        ->create();

    $data = [
        'name' => 'الاسم الجديد',
        'capacity' => 35,
    ];

    $response = $this->actingAs($this->teacher)
        ->putJson("/api/education/halaqat/{$halaqa->id}", $data);

    $response->assertStatus(200);
});

test('supervisor cannot update other mosque halaqa', function () {
    $otherMosque = Mosque::factory()->create();
    $halaqa = Halaqa::factory()
        ->forMosque($otherMosque)
        ->create();

    $response = $this->actingAs($this->supervisor)
        ->putJson("/api/education/halaqat/{$halaqa->id}", ['name' => 'جديد']);

    $response->assertStatus(404);
});

// ========================
// اختبارات DELETE
// ========================

test('supervisor can delete their mosque halaqa', function () {
    $halaqa = Halaqa::factory()
        ->forMosque($this->mosque)
        ->create();

    $response = $this->actingAs($this->supervisor)
        ->deleteJson("/api/education/halaqat/{$halaqa->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('halaqats', ['id' => $halaqa->id]);
});

test('teacher can delete their halaqa', function () {
    $halaqa = Halaqa::factory()
        ->forMosque($this->mosque)
        ->withTeacher($this->teacher)
        ->create();

    $response = $this->actingAs($this->teacher)
        ->deleteJson("/api/education/halaqat/{$halaqa->id}");

    $response->assertStatus(200);
});

// ========================
// اختبارات ATTACH STUDENTS
// ========================

test('can attach students to halaqa', function () {
    $halaqa = Halaqa::factory()
        ->forMosque($this->mosque)
        ->state(['capacity' => 10])
        ->create();

    $students = Student::factory()
        ->forMosque($this->mosque)
        ->count(3)
        ->create();

    $studentIds = $students->pluck('id')->toArray();

    $response = $this->actingAs($this->supervisor)
        ->postJson("/api/education/halaqat/{$halaqa->id}/students", [
            'students' => $studentIds,
        ]);

    $response->assertStatus(200);

    foreach ($studentIds as $studentId) {
        $this->assertDatabaseHas('halaqa_student', [
            'halaqa_id' => $halaqa->id,
            'student_id' => $studentId,
            'status' => 'active',
        ]);
    }
});

test('cannot attach student from different mosque', function () {
    $halaqa = Halaqa::factory()
        ->forMosque($this->mosque)
        ->create();

    $otherMosque = Mosque::factory()->create();
    $student = Student::factory()->forMosque($otherMosque)->create();

    $response = $this->actingAs($this->supervisor)
        ->postJson("/api/education/halaqat/{$halaqa->id}/students", [
            'students' => [$student->id],
        ]);

    $response->assertStatus(422);
    $response->assertInvalid('students');
});

test('cannot attach inactive student', function () {
    $halaqa = Halaqa::factory()
        ->forMosque($this->mosque)
        ->create();

    $student = Student::factory()
        ->forMosque($this->mosque)
        ->inactive()
        ->create();

    $response = $this->actingAs($this->supervisor)
        ->postJson("/api/education/halaqat/{$halaqa->id}/students", [
            'students' => [$student->id],
        ]);

    $response->assertStatus(422);
    $response->assertInvalid('students');
});

test('cannot attach student already in halaqa', function () {
    $halaqa = Halaqa::factory()
        ->forMosque($this->mosque)
        ->create();

    $student = Student::factory()
        ->forMosque($this->mosque)
        ->create();

    $halaqa->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]);

    $response = $this->actingAs($this->supervisor)
        ->postJson("/api/education/halaqat/{$halaqa->id}/students", [
            'students' => [$student->id],
        ]);

    $response->assertStatus(422);
    $response->assertInvalid('students');
});

test('cannot attach students exceeding capacity', function () {
    $halaqa = Halaqa::factory()
        ->forMosque($this->mosque)
        ->state(['capacity' => 2])
        ->create();

    $existingStudent = Student::factory()
        ->forMosque($this->mosque)
        ->create();
    $halaqa->students()->attach($existingStudent->id, ['status' => 'active', 'joined_at' => now()]);

    $newStudents = Student::factory()
        ->forMosque($this->mosque)
        ->count(2)
        ->create();

    $response = $this->actingAs($this->supervisor)
        ->postJson("/api/education/halaqat/{$halaqa->id}/students", [
            'students' => $newStudents->pluck('id')->toArray(),
        ]);

    $response->assertStatus(422);
    $response->assertInvalid('capacity');
});

test('cannot attach nonexistent student', function () {
    $halaqa = Halaqa::factory()
        ->forMosque($this->mosque)
        ->create();

    $response = $this->actingAs($this->supervisor)
        ->postJson("/api/education/halaqat/{$halaqa->id}/students", [
            'students' => [9999],
        ]);

    $response->assertStatus(422);
    $response->assertInvalid('students');
});

test('validation fails with empty student array', function () {
    $halaqa = Halaqa::factory()
        ->forMosque($this->mosque)
        ->create();

    $response = $this->actingAs($this->supervisor)
        ->postJson("/api/education/halaqat/{$halaqa->id}/students", [
            'students' => [],
        ]);

    $response->assertStatus(422);
});

// ========================
// اختبارات DETACH STUDENT
// ========================

test('can detach student from halaqa', function () {
    $halaqa = Halaqa::factory()
        ->forMosque($this->mosque)
        ->create();

    $student = Student::factory()
        ->forMosque($this->mosque)
        ->create();

    $halaqa->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]);

    $response = $this->actingAs($this->supervisor)
        ->deleteJson("/api/education/halaqat/{$halaqa->id}/students/{$student->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('halaqa_student', [
        'halaqa_id' => $halaqa->id,
        'student_id' => $student->id,
    ]);
});

test('cannot detach student not in halaqa', function () {
    $halaqa = Halaqa::factory()
        ->forMosque($this->mosque)
        ->create();

    $student = Student::factory()
        ->forMosque($this->mosque)
        ->create();

    $response = $this->actingAs($this->supervisor)
        ->deleteJson("/api/education/halaqat/{$halaqa->id}/students/{$student->id}");

    $response->assertStatus(422);
    $response->assertInvalid('student');
});

// ========================
// اختبارات الصلاحيات
// ========================

test('unauthenticated user cannot access halaqat', function () {
    $response = $this->getJson('/api/education/halaqat');

    expect($response->status())->toBeIn([401, 403]);
});

test('user without role cannot access halaqat', function () {
    $regularUser = User::factory()->create();

    $response = $this->actingAs($regularUser)
        ->getJson('/api/education/halaqat');

    expect($response->status())->toBeIn([403, 404]);
});
