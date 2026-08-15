<?php

namespace Modules\Dashboard\Services;

use Carbon\Carbon;
use Modules\Education\Models\Student;
use Modules\User\Models\User;
use Modules\Complaint\Models\Complaint;
use Modules\Donation\Models\Donation;
use Modules\Education\Models\Attendance;
use Modules\Complaint\Service\ComplaintService;
use Modules\MaintenanceRequest\Service\MaintenanceService;
use Modules\Mosque\Services\MosqueTaskService;

class MosqueDashboardService
{

    public function __construct(
        private readonly ComplaintService $complaintService,
        private readonly MaintenanceService $maintenanceService,
        private readonly MosqueTaskService $taskService,
    ) {}
    
    public function getDashboardData(User $user): array
    {
        $mosqueId = $user->mosque_id;
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $previousMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        return [
            'welcome_message'   => "السلام عليكم، {$user->name}",
            'date_formatted'    => $today->locale('ar')->translatedFormat('l d F Y'),
            'kpi_cards'         => $this->getKpiCards($mosqueId, $today, $startOfMonth, $previousMonthStart, $previousMonthEnd),
            'attendance_chart'  => $this->getAttendanceChart($mosqueId),
            'recent_activities' => $this->getRecentActivities($mosqueId),
            'today_tasks'        => $this->getTodayTasksWidget($mosqueId),
            'latest_requests'    => $this->getLatestComplaintsAndRequests($mosqueId),
        ];
    }

    private function getKpiCards($mosqueId, $today, $startOfMonth, $previousMonthStart, $previousMonthEnd): array
    {
        // 1. إجمالي الطلاب من جدول students (إما عبر mosque_id مباشر أو عبر حلقاته)
        $totalStudents = Student::whereHas('halaqats', function ($q) use ($mosqueId) {
            $q->where('mosque_id', $mosqueId);
        })->count();
        // ملاحظة: إذا كان جدول students يحتوي على mosque_id مباشرة، استخدم:
        // $totalStudents = Student::where('mosque_id', $mosqueId)->count();

        // 2. إجمالي المعلمين
        $totalTeachers = User::role('teacher')
            ->where('mosque_id', $mosqueId)
            ->count();

        // 3. نسبة حضور اليوم
        $todayTotalAttendance = Attendance::whereHas('halaqa', function ($query) use ($mosqueId) {
            $query->where('mosque_id', $mosqueId);
        })->whereDate('date', $today)->count();

        $todayPresentCount = Attendance::whereHas('halaqa', function ($query) use ($mosqueId) {
            $query->where('mosque_id', $mosqueId);
        })->whereDate('date', $today)->where('status', 'present')->count();

        $todayAttendanceRate = $todayTotalAttendance > 0
            ? round(($todayPresentCount / $todayTotalAttendance) * 100)
            : 0;

        // 4. التبرعات
        $currentMonthDonations = Donation::where('mosque_id', $mosqueId)
            ->whereIn('status', ['paid', 'completed', 'approved'])
            ->where('created_at', '>=', $startOfMonth)
            ->sum('amount');

        $lastMonthDonations = Donation::where('mosque_id', $mosqueId)
            ->whereIn('status', ['paid', 'completed', 'approved'])
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->sum('amount');

        $donationChangePercentage = $lastMonthDonations > 0
            ? round((($currentMonthDonations - $lastMonthDonations) / $lastMonthDonations) * 100, 1)
            : 0;

        return [
            'total_students' => [
                'value' => $totalStudents,
                'percentage_change' => '+12%',
                'is_increase' => true
            ],
            'total_teachers' => [
                'value' => $totalTeachers,
                'percentage_change' => '+2',
                'is_increase' => true
            ],
            'today_attendance' => [
                'value' => "%{$todayAttendanceRate}",
                'percentage_change' => '+5%',
                'is_increase' => true
            ],
            'monthly_donations' => [
                'value' => (float) $currentMonthDonations,
                'formatted_value' => number_format($currentMonthDonations) . ' ر.س',
                'percentage_change' => ($donationChangePercentage >= 0 ? '+' : '') . $donationChangePercentage . '%',
                'is_increase' => $donationChangePercentage >= 0
            ]
        ];
    }

    private function getAttendanceChart($mosqueId): array
    {
        $attendanceChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            $dayTotal = Attendance::whereHas('halaqa', function ($q) use ($mosqueId) {
                $q->where('mosque_id', $mosqueId);
            })->whereDate('date', $date)->count();

            $dayPresent = Attendance::whereHas('halaqa', function ($q) use ($mosqueId) {
                $q->where('mosque_id', $mosqueId);
            })->whereDate('date', $date)->where('status', 'present')->count();

            $rate = $dayTotal > 0 ? round(($dayPresent / $dayTotal) * 100) : 0;

            $attendanceChart[] = [
                'day'  => $date->locale('ar')->dayName,
                'rate' => $rate,
            ];
        }

        return [
            'filter' => 'آخر 7 أيام',
            'series' => $attendanceChart
        ];
    }

    private function getRecentActivities($mosqueId)
    {
        // 1. تسجيل الطلاب الجدد من جدول students
        $students = Student::whereHas('halaqats', function ($q) use ($mosqueId) {
            $q->where('mosque_id', $mosqueId);
        })
            ->latest()
            ->take(3)
            ->get()
            ->map(function ($s) {
                $studentName = trim("{$s->first_name} {$s->last_name}") ?: $s->name;
                return [
                    'id'         => 'std_' . $s->id,
                    'title'      => "{$studentName} انضم إلى حلقة التحفيظ",
                    'created_at' => $s->created_at,
                    'type'       => 'student_registration',
                    'icon'       => 'user_add'
                ];
            });

        // 2. الشكاوى والصيانة
        $complaints = Complaint::where('mosque_id', $mosqueId)
            ->latest()
            ->take(3)
            ->get()
            ->map(fn($c) => [
                'id'         => 'cmp_' . $c->id,
                'title'      => "نظام الصيانة: {$c->title}",
                'created_at' => $c->created_at,
                'type'       => 'complaint',
                'icon'       => 'wrench'
            ]);

        // 3. التبرعات
        $donations = Donation::where('mosque_id', $mosqueId)
            ->latest()
            ->take(3)
            ->get()
            ->map(fn($d) => [
                'id'         => 'don_' . $d->id,
                'title'      => "إدارة التبرعات: تبرع جديد بقيمة {$d->amount} ر.س",
                'created_at' => $d->created_at,
                'type'       => 'donation',
                'icon'       => 'money'
            ]);

        // دمج كافة النشاطات وفرزها زمنياً
        return collect()
            ->concat($students)
            ->concat($complaints)
            ->concat($donations)
            ->sortByDesc('created_at')
            ->take(5)
            ->values()
            ->map(function ($item) {
                $item['time_ago'] = Carbon::parse($item['created_at'])->locale('ar')->diffForHumans();
                unset($item['created_at']);
                return $item;
            });
    }

    private function getTodayTasksWidget(int $mosqueId): array
    {
        $todayDate = Carbon::today()->toDateString();

        return [
            'date_tabs' => $this->taskService->dateTabs($mosqueId),
            'selected'  => $this->taskService->listForDate($mosqueId, $todayDate, null, null),
        ];
    }

    private function getLatestComplaintsAndRequests(int $mosqueId, int $limit = 5): array
    {
        $complaints = collect($this->complaintService->getRecentComplaints($mosqueId, $limit))
            ->map(fn($c) => [
                'id'         => 'cmp_' . $c['id'],
                'title'      => $c['title'],
                'department' => $c['complaint_type'] ?? null,
                'priority'   => $c['priority'],
                'status'     => $c['status'],
                'type'       => 'complaint',
                'created_at' => $c['created_at'],
            ]);

        $maintenanceRequests = collect($this->maintenanceService->getRecentRequests($mosqueId, $limit))
            ->map(fn($m) => [
                'id'         => 'mnt_' . $m['id'],
                'title'      => $m['title'],
                'department' => $m['category'] ?? null,
                'priority'   => $m['priority'],
                'status'     => $m['status'],
                'type'       => 'maintenance',
                'created_at' => $m['created_at'],
            ]);

        return $complaints
            ->concat($maintenanceRequests)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values()
            ->all();
    }
}
