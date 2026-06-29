<?php

namespace Modules\Mosque\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class MosqueReportingEtlService
{
    public function generateDailyMosqueInsights(int $days = 1): array
    {
        $days = max(1, $days);
        [$startDate, $endDate] = $this->buildWindow($days);

        $dates = $this->buildDates($startDate, $endDate);
        $this->populateTimeDimension($dates);
        $this->populateMosqueDimension();

        $factRows = $this->buildFactRows($startDate, $endDate);
        $this->writeFactRows($factRows);

        return [
            'days_processed' => $dates->count(),
            'fact_rows' => count($factRows),
            'mosque_dimensions' => DB::table('dw_dim_mosque')->count(),
        ];
    }

    private function buildWindow(int $days): array
    {
        $endDate = Carbon::yesterday();
        $startDate = $endDate->copy()->subDays($days - 1);

        return [$startDate, $endDate];
    }

    private function buildDates(Carbon $startDate, Carbon $endDate)
    {
        return collect(CarbonPeriod::create($startDate, $endDate))
            ->map(fn(Carbon $date) => $date->copy()->startOfDay());
    }

    private function populateTimeDimension($dates): void
    {
        $rows = $dates->map(function (Carbon $date) {
            return [
                'time_id' => (int) $date->format('Ymd'),
                'full_date' => $date->toDateString(),
                'day_of_week' => (int) $date->dayOfWeek,
                'month' => (int) $date->month,
                'quarter' => (int) $date->quarter,
                'year' => (int) $date->year,
                'is_weekend' => $date->isWeekend(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->all();

        DB::table('dw_dim_time')->upsert(
            $rows,
            ['time_id'],
            ['full_date', 'day_of_week', 'month', 'quarter', 'year', 'is_weekend', 'updated_at']
        );
    }

    private function populateMosqueDimension(): void
    {
        $batch = [];

        DB::table('mosques')
            ->select(['id', 'name', 'city', 'district', 'status'])
            ->orderBy('id')
            ->lazyById(500)
            ->each(function ($mosque) use (&$batch) {
                $batch[] = [
                    'mosque_id' => $mosque->id,
                    'mosque_name' => $mosque->name,
                    'city' => $mosque->city,
                    'district' => $mosque->district,
                    'status' => $mosque->status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($batch) >= 500) {
                    $this->flushMosqueDimensionBatch($batch);
                    $batch = [];
                }
            });

        if (! empty($batch)) {
            $this->flushMosqueDimensionBatch($batch);
        }
    }

    private function flushMosqueDimensionBatch(array $batch): void
    {
        DB::table('dw_dim_mosque')->upsert(
            $batch,
            ['mosque_id'],
            ['mosque_name', 'city', 'district', 'status', 'updated_at']
        );
    }

    private function buildFactRows(Carbon $startDate, Carbon $endDate): array
    {
        $donationRows = $this->buildDonationRows($startDate, $endDate);
        $attendanceRows = $this->buildAttendanceRows($startDate, $endDate);
        $campaignRows = $this->buildCampaignRows($startDate, $endDate);
        $halaqaRows = $this->buildHalaqaRows();

        // 1. استخراج التواريخ كنصوص لاستخدامها في بناء المفاتيح
        $dates = $this->buildDates($startDate, $endDate)->map(fn($d) => $d->toDateString())->toArray();

        // 2. إصلاح المشكلة: توليد مفاتيح (مسجد|تاريخ) لجميع المساجد التي تحتوي على حلقات
        $halaqaCompositeKeys = [];
        foreach ($halaqaRows as $mosqueId => $count) {
            foreach ($dates as $dateString) {
                $halaqaCompositeKeys[] = "{$mosqueId}|{$dateString}";
            }
        }

        // 3. دمج جميع المفاتيح بشكل آمن الآن (كلها تحتوي على | )
        $keys = array_unique(array_merge(
            array_keys($donationRows),
            array_keys($attendanceRows),
            array_keys($campaignRows),
            $halaqaCompositeKeys // استخدام المفاتيح المركبة بدلاً من array_keys($halaqaRows)
        ));

        $rows = [];

        foreach ($keys as $key) {
            [$mosqueId, $dateString] = explode('|', $key);
            $timeId = (int) Carbon::parse($dateString)->format('Ymd');

            $attendance = $attendanceRows[$key] ?? ['present_count' => 0, 'total_count' => 0];
            $attendanceRate = $attendance['total_count'] > 0
                ? round($attendance['present_count'] / $attendance['total_count'], 4)
                : 0;

            $rows[] = [
                'mosque_id' => (int) $mosqueId,
                'time_id' => $timeId,
                'total_donations_collected' => $donationRows[$key]['total_donations_collected'] ?? 0,
                'total_net_amount' => $donationRows[$key]['total_net_amount'] ?? 0,
                'active_campaigns_count' => $campaignRows[$key] ?? 0,

                'active_halaqas_count' => $halaqaRows[$mosqueId] ?? 0,

                'average_attendance_rate' => $attendanceRate,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return $rows;
    }
    private function buildDonationRows(Carbon $startDate, Carbon $endDate): array
    {
        $rows = DB::table('donations')
            ->selectRaw(
                "mosque_id, DATE(completed_at) AS donation_date, COUNT(*) AS total_donations_collected, COALESCE(SUM(COALESCE(base_amount, amount, 0)), 0) AS total_net_amount"
            )
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereBetween(DB::raw('DATE(completed_at)'), [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('mosque_id', 'donation_date')
            ->get()
            ->mapWithKeys(function ($row) {
                $key = "{$row->mosque_id}|{$row->donation_date}";

                return [$key => [
                    'total_donations_collected' => (int) $row->total_donations_collected,
                    'total_net_amount' => (float) $row->total_net_amount,
                ]];
            })
            ->all();

        return $rows;
    }

    private function buildAttendanceRows(Carbon $startDate, Carbon $endDate): array
    {
        $rows = DB::table('attendances')
            ->join('halaqats', 'attendances.halaqa_id', '=', 'halaqats.id')
            ->selectRaw(
                'halaqats.mosque_id, attendances.date AS attendance_date,' .
                    "SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) AS present_count, " .
                    'COUNT(*) AS total_count'
            )
            ->whereBetween('attendances.date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('halaqats.mosque_id', 'attendances.date')
            ->get()
            ->mapWithKeys(function ($row) {
                $key = "{$row->mosque_id}|{$row->attendance_date}";

                return [$key => [
                    'present_count' => (int) $row->present_count,
                    'total_count' => (int) $row->total_count,
                ]];
            })
            ->all();

        return $rows;
    }

    private function buildCampaignRows(Carbon $startDate, Carbon $endDate): array
    {
        $dates = $this->buildDates($startDate, $endDate)->map(fn(Carbon $date) => $date->toDateString());

        $campaigns = DB::table('campaigns')
            ->select(['mosque_id', 'start_date', 'end_date'])
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $endDate)
            ->where(function ($query) use ($startDate) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $startDate);
            })
            ->get();

        $rows = [];

        foreach ($campaigns as $campaign) {
            foreach ($dates as $dateString) {
                if ($campaign->start_date > $dateString) {
                    continue;
                }

                if ($campaign->end_date !== null && $campaign->end_date < $dateString) {
                    continue;
                }

                $key = "{$campaign->mosque_id}|{$dateString}";
                $rows[$key] = ($rows[$key] ?? 0) + 1;
            }
        }

        return $rows;
    }

    private function buildHalaqaRows(): array
    {
        return DB::table('halaqats')
            ->selectRaw('mosque_id, COUNT(*) AS active_halaqas_count')
            ->where('status', 'active')
            ->groupBy('mosque_id')
            ->get()
            ->mapWithKeys(function ($row) {
                return [
                    (string) $row->mosque_id => (int) $row->active_halaqas_count,
                ];
            })
            ->all();
    }

    private function writeFactRows(array $rows): void
    {
        $chunkSize = 200;
        $batch = [];

        foreach ($rows as $row) {
            $batch[] = $row;

            if (count($batch) >= $chunkSize) {
                $this->flushFactBatch($batch);
                $batch = [];
            }
        }

        if (! empty($batch)) {
            $this->flushFactBatch($batch);
        }
    }

    private function flushFactBatch(array $batch): void
    {
        DB::transaction(function () use ($batch) {
            DB::table('dw_fact_mosque_performance')->upsert(
                $batch,
                ['mosque_id', 'time_id'],
                [
                    'total_donations_collected',
                    'total_net_amount',
                    'active_campaigns_count',
                    'active_halaqas_count',
                    'average_attendance_rate',
                    'updated_at',
                ]
            );
        });
    }
}
