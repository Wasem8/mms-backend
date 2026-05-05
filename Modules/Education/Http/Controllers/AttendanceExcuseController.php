<?php

namespace Modules\Education\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Education\Actions\ProcessExcuseAction;
use Modules\Education\Http\Requests\StoreExcuseRequest;
use Modules\Education\Models\AttendanceExcuse;

class AttendanceExcuseController extends Controller
{

    public function store(StoreExcuseRequest $request)
    {
        $excuse = AttendanceExcuse::create([
            'parent_id'    => auth()->id(),
            'student_id'   => $request->student_id,
            'halaqa_id'    => $request->halaqa_id,
            'absence_date' => $request->absence_date,
            'reason'       => $request->reason,
            'status'       => 'pending',
        ]);

        // هنا يفضل إرسال إشعار للمعلم (Notification)
        // Notification::send($excuse->halaqa->teacher, new NewExcuseNotification($excuse));

        return ApiResponse::success(
            [],
            'تم إرسال عذر الغياب بنجاح، وسيتم إبلاغ المعلم.'
        );
    }


    public function myExcuses()
    {
        $excuses = AttendanceExcuse::with(['student:id,first_name,last_name', 'halaqa:id,name'])
            ->where('parent_id', auth()->id())
            ->latest()
            ->paginate(15);

        return ApiResponse::success(
            $excuses->items(),
            'تم جلب البيانات بنجاح.',
            $excuses
        );
    }


    public function indexForTeacher(Request $request)
    {
        $user = auth()->user();

        $query = AttendanceExcuse::with(['student:id,first_name,last_name', 'halaqa:id,name'])
            ->whereHas('halaqa', function ($q) use ($user) {
                $q->where('teacher_id', $user->id);
            });

        if ($request->has('status') && in_array($request->status, ['pending', 'accepted', 'rejected'])) {
            $query->where('status', $request->status);
        }

        if ($request->has('date')) {
            $query->whereDate('absence_date', $request->date);
        }

        $excuses = $query->latest()->paginate(15);

        return ApiResponse::success($excuses->items(), 'تم جلب طلبات الأعذار بنجاح.', $excuses);
    }

    public function process(Request $request, $id, ProcessExcuseAction $action)
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected',
            'admin_comment' => 'nullable|string|max:500',
        ]);

        $excuse = AttendanceExcuse::findOrFail($id);

        if ($excuse->halaqa->teacher_id !== auth()->id()) {
            return ApiResponse::error('غير مصرح لك بمعالجة هذا الطلب', 403);
        }

        if ($excuse->status !== 'pending') {
            return ApiResponse::error('هذا العذر تم معالجته مسبقاً', 422);
        }

        $action->execute($excuse, $request->only(['status', 'admin_comment']));

        return ApiResponse::success([], 'تم تحديث حالة العذر وتحديث سجل الحضور بنجاح.');
    }

}
