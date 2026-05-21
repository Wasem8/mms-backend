<?php

namespace Modules\Education\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Education\Models\Halaqa;
use Modules\User\Models\User;

class TeacherService
{

    public function getTeachersList()
    {
        $user = auth()->user();

        $query = User::role('teacher')
            ->with([
                'teacherProfile' => function($q) {
                    // 🎯 أضف الحقول المفقودة هنا ليتم سحبها من قاعدة البيانات
                    $q->select('id', 'user_id', 'phone', 'status', 'specialization', 'notes');
                }
            ])
            ->withCount('halaqats');

        match (true) {
            $user->isAreaManager() => null,
            $user->isMosqueManager() || $user->isSupervisor() => $query->where('mosque_id', $user->mosque_id),
            $user->isTeacher() => $query->where('id', $user->id),
            default => $query->whereRaw('1 = 0'),
        };

        return $query->latest()
            ->get(['id', 'name', 'email', 'mosque_id', 'created_at']);
    }


    public function getTeacherDetails($teacherId)
    {
        $user = auth()->user();

        $query = User::role('teacher')
            ->with([
                'teacherProfile',
                'halaqats' => function($query) {
                    $query->withCount([
                        'students',
                        'attendances as total_absent_count' => fn($q) => $q->whereIn('status', ['absent', 'absent_with_excuse']),
                        'attendances as total_present_count' => fn($q) => $q->where('status', 'present')
                    ]);
                }
            ]);

        if (!$user->isAreaManager()) {
            $query->where('mosque_id', $user->mosque_id);
        }

        return $query->findOrFail($teacherId);
    }
    public function updateTeacher(int $id, array $data): User
    {
        return DB::transaction(function () use ($id, $data) {
            // 1. جلب المستخدم والتأكد من نطاق مسجد المشرف الحالي
            $user = User::where('mosque_id', auth()->user()->mosque_id)
                ->findOrFail($id);

            // 2. التحقق الصارم من أن الحساب يمتلك دور معلم وليس دوراً آخر (مثل مشرف أو ولي أمر)
            if (!$user->hasRole('teacher')) {
                throw ValidationException::withMessages([
                    'teacher' => __('messages.user_is_not_a_teacher / الحساب المحدد ليس لمعلم.')
                ]);
            }

            // 3. تحديث الاسم في جدول users الأساسي إذا تم تمريره
            if (isset($data['name'])) {
                $user->update(['name' => $data['name']]);
            }

            // 4. تحديث أو إنشاء البروفايل المنفصل (الحقول والـ Enum)
            $user->teacherProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'phone'          => $data['phone'] ?? $user->teacherProfile?->phone,
                    'specialization' => $data['specialization'] ?? $user->teacherProfile?->specialization,
                    'status'         => $data['status'] ?? $user->teacherProfile?->status ?? 'active',
                    'notes'          => $data['notes'] ?? $user->teacherProfile?->notes,
                ]
            );

            // إرجاع المعلم محمل بالعلاقات المطلوبة للعرض (دون الحلقات كونه لم يتم تعديلها هنا)
            return $user->load(['teacherProfile']);
        });
    }
}
