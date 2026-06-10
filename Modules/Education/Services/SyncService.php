<?php

namespace Modules\Education\Services;

use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Education\Models\AttendanceExcuse;
use Modules\Education\Actions\ProcessExcuseAction;
use Modules\Education\Transformers\AttendanceResource;
use Modules\Education\Transformers\EvaluationResource;
use Modules\Education\Transformers\AttendanceExcuseResource;

class SyncService
{
    public function __construct(
        private AttendanceService $attendanceService,
        private EvaluationService $evaluationService,
        private ProcessExcuseAction $processExcuseAction
    ) {}

    public function sync(array $ops): array
    {
        $results = [];

        foreach ($ops as $op) {
            $type = $op['type'] ?? null;
            $uuid = $op['client_uuid'] ?? null;
            $data = $op['data'] ?? [];

            try {
                if (!$type || !$uuid) {
                    $results[] = $this->error($uuid, 'missing type or uuid', 'VALIDATION_ERROR');
                    continue;
                }

                switch ($type) {
                    case 'attendance':
                        $results[] = $this->handleAttendanceSync($uuid, $data);
                        break;

                    case 'evaluation':
                        $results[] = $this->handleEvaluationSync($uuid, $data);
                        break;

                    case 'excuse_decision':
                        $results[] = $this->handleExcuseDecisionSync($uuid, $data);
                        break;

                    default:
                        $results[] = $this->error($uuid, 'unknown type', 'UNKNOWN_TYPE');
                }

            } catch (ModelNotFoundException $e) {
                // 🎯 اصطياد أخطاء الـ findOrFail المرمية من داخل الـ Services (مثل عدم وجود الحلقة أو الطالب)
                $errorCode = $this->guessErrorCodeFromException($e);
                $results[] = $this->error(
                    $uuid,
                    'المورد المطلوب غير موجود بالسيرفر، ربما تم حذفه مسبقاً.',
                    $errorCode
                );
            } catch (Throwable $e) {
                // خطأ غير متوقع في النظام
                $results[] = $this->error($uuid, $e->getMessage(), 'SERVER_ERROR');
            }
        }

        return $results;
    }

    /**
     * Attendance
     */
    private function handleAttendanceSync(string $uuid, array $data): array
    {
        if (empty($data['halaqa_id']) || empty($data['date'])) {
            return $this->error($uuid, 'missing attendance required fields', 'VALIDATION_ERROR');
        }

        // إذا كان كود الـ Service لديك يستعمل findOrFail للـ Halaqa، الـ catch بالأعلى سيصطاده تلقائياً
        $result = $this->attendanceService->storeBulk($data);

        return [
            'client_uuid' => $uuid,
            'status' => 'ok',
            'data' => $result,
            'message' => 'attendance saved'
        ];
    }

    /**
     * Evaluation
     */
    private function handleEvaluationSync(string $uuid, array $data): array
    {
        if (empty($data['halaqa_id']) || empty($data['student_id']) || !isset($data['score'])) {
            return $this->error($uuid, 'missing evaluation required fields', 'VALIDATION_ERROR');
        }

        $result = $this->evaluationService->store([
            ...$data,
            'client_uuid' => $uuid,
            'evaluated_at' => $data['evaluated_at'] ?? now(), // يعتمد تاريخ العميل الممرر بصيغة ISO 8601 الكاملة
            'synced_at' => now(),
        ]);

        return [
            'client_uuid' => $uuid,
            'status' => $result['is_duplicate'] ? 'conflict' : 'ok',
            'data' => isset($result['evaluation']) ? new EvaluationResource($result['evaluation']) : null,
            'message' => $result['is_duplicate'] ? 'already exists' : 'evaluation saved',
        ];
    }

    /**
     * Excuse Decision
     */
    private function handleExcuseDecisionSync(string $uuid, array $data): array
    {
        if (empty($data['excuse_id']) || empty($data['status'])) {
            return $this->error($uuid, 'missing excuse decision fields', 'VALIDATION_ERROR');
        }

        $excuse = AttendanceExcuse::find($data['excuse_id']);

        if (!$excuse) {
            return $this->error($uuid, 'excuse not found', 'EXCUSE_NOT_FOUND');
        }

        // تحصين الصلاحيات وعزل البيانات
        if ($excuse->halaqa->teacher_id !== auth()->id()) {
            return $this->error($uuid, 'forbidden', 'FORBIDDEN');
        }

        /**
         * Replay-safe logic inside sync aggregator
         */
        if ($excuse->status !== 'pending') {
            return [
                'client_uuid' => $uuid,
                'status' => 'ok', // نرسل ok بناءً على الاتفاق لمنع التسبب في أخطاء فرونت إند
                'data' => [
                    'code' => 'EXCUSE_ALREADY_PROCESSED',
                    'status' => $excuse->status,
                    'processed_at' => $excuse->processed_at,
                    'final' => true,
                ],
                'message' => 'already processed',
            ];
        }

        $this->processExcuseAction->execute(
            $excuse,
            [
                'status' => $data['status'],
                'admin_comment' => $data['admin_comment'] ?? null,
                'processed_at' => $data['processed_at'] ?? now(), // 🎯 يعتمد تاريخ اتخاذ القرار الفعلي من المعلم وهو أوفلاين
            ]
        );

        $excuse->refresh();

        return [
            'client_uuid' => $uuid,
            'status' => 'ok',
            'data' => new AttendanceExcuseResource($excuse),
            'message' => 'excuse processed',
        ];
    }

    /**
     * Error helper formatting
     */
    private function error($uuid, string $message, ?string $code = null): array
    {
        return [
            'client_uuid' => $uuid,
            'status' => 'error',
            'data' => $code ? ['code' => $code] : null,
            'message' => $message,
        ];
    }

    /**
     * استخراج وتحديد كود الخطأ البرمي بناءً على اسم الموديل المفقود
     */
    private function guessErrorCodeFromException(ModelNotFoundException $e): string
    {
        $model = $e->getModel();

        if (str_contains($model, 'Halaqa')) {
            return 'HALAQA_NOT_FOUND';
        }

        if (str_contains($model, 'Student')) {
            return 'STUDENT_NOT_FOUND';
        }

        return 'RESOURCE_NOT_FOUND';
    }
}
