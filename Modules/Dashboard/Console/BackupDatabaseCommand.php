<?php

namespace Modules\Dashboard\Console;

use Illuminate\Console\Command;
use Modules\Dashboard\Services\BackupService;
use Throwable;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'backup:database
                            {--triggered-by=manual : manual or scheduled}';

    protected $description =
        'Create PostgreSQL database backup and upload it to Supabase Storage';

    public function handle(
        BackupService $backupService
    ): int {

        $this->info('');
        $this->info(
            '🔄 بدء النسخ الاحتياطي لقاعدة البيانات...'
        );

        $this->info(
            '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'
        );

        try {

            $backup = $backupService->createDatabaseBackup(
                (string) $this->option('triggered-by')
            );

            $this->info(
                '✅ تم إنشاء النسخة الاحتياطية بنجاح.'
            );

            $this->info(
                '📁 الملف: ' .
                $backup->file_name
            );

            $this->info(
                '📦 الحجم: ' .
                $this->formatBytes(
                    $backup->file_size
                )
            );

            $this->info(
                '🔐 SHA-256: ' .
                $backup->checksum
            );

            $this->info(
                '☁️ المسار: ' .
                $backup->storage_path
            );

            $this->info(
                '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'
            );

            return self::SUCCESS;

        } catch (Throwable $e) {

            $this->error(
                '❌ فشل النسخ الاحتياطي.'
            );

            $this->error(
                '📍 ' .
                $e->getMessage()
            );

            return self::FAILURE;
        }
    }

    private function formatBytes(
        ?int $bytes
    ): string {

        if ($bytes === null) {
            return 'غير معروف';
        }

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1024 ** 2) {
            return round(
                    $bytes / 1024,
                    2
                ) . ' KB';
        }

        if ($bytes < 1024 ** 3) {
            return round(
                    $bytes / (1024 ** 2),
                    2
                ) . ' MB';
        }

        return round(
                $bytes / (1024 ** 3),
                2
            ) . ' GB';
    }
}
