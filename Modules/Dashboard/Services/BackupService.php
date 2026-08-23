<?php

namespace Modules\Dashboard\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Modules\Dashboard\Models\BackupLog;
use Throwable;

class BackupService
{
    public function createDatabaseBackup(
        string $triggeredBy = 'manual'
    ): BackupLog {

        $startedAt = now();

        $fileName =
            'database-' .
            $startedAt->format('Y-m-d_H-i-s') .
            '.dump';

        $storagePath =
            'database/' .
            $startedAt->format('Y/m') .
            '/' .
            $fileName;

        $backupLog = BackupLog::create([
            'file_name' => $fileName,
            'storage_path' => $storagePath,
            'status' => 'running',
            'triggered_by' => $triggeredBy,
            'started_at' => $startedAt,
        ]);

        $tempDirectory = storage_path('app/backup-temp');

        if (!is_dir($tempDirectory)) {
            mkdir(
                $tempDirectory,
                0755,
                true
            );
        }

        $localPath =
            $tempDirectory .
            DIRECTORY_SEPARATOR .
            $fileName;

        try {

            /*
            |--------------------------------------------------------------------------
            | 1. Validate configuration
            |--------------------------------------------------------------------------
            */

            $this->validateConfiguration();

            /*
            |--------------------------------------------------------------------------
            | 2. Create PostgreSQL dump
            |--------------------------------------------------------------------------
            |
            | -Fc = PostgreSQL custom format
            | -Z 6 = compression level
            |
            */

            $pgDump =
                config(
                    'services.supabase.backup_pg_dump_path',
                    env(
                        'BACKUP_PG_DUMP_PATH',
                        'pg_dump'
                    )
                );

            $host =
                env(
                    'BACKUP_DB_HOST',
                    config('database.connections.pgsql.host')
                );

            $port =
                env(
                    'BACKUP_DB_PORT',
                    config('database.connections.pgsql.port', 5432)
                );

            $database =
                env(
                    'BACKUP_DB_DATABASE',
                    config('database.connections.pgsql.database')
                );

            $username =
                env(
                    'BACKUP_DB_USERNAME',
                    config('database.connections.pgsql.username')
                );

            $password =
                env(
                    'BACKUP_DB_PASSWORD',
                    config('database.connections.pgsql.password')
                );

            /*
            |--------------------------------------------------------------------------
            | Process
            |--------------------------------------------------------------------------
            */

            $command =
                sprintf(
                    '"%s" --host="%s" --port="%s" --username="%s" --dbname="%s" --format=custom --compress=6 --file="%s"',
                    $pgDump,
                    $host,
                    $port,
                    $username,
                    $database,
                    $localPath
                );

            $timeout =
                (int) env(
                    'BACKUP_TIMEOUT',
                    600
                );

            $result = Process::timeout($timeout)
                ->env([
                    'PGPASSWORD' => $password,
                ])
                ->run($command);

            if ($result->failed()) {

                throw new \RuntimeException(
                    'pg_dump failed: ' .
                    trim(
                        $result->errorOutput()
                    )
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Verify backup file
            |--------------------------------------------------------------------------
            */

            if (!file_exists($localPath)) {

                throw new \RuntimeException(
                    'Backup file was not created.'
                );
            }

            $fileSize =
                filesize($localPath);

            if ($fileSize === false || $fileSize === 0) {

                throw new \RuntimeException(
                    'Backup file is empty.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 4. Calculate SHA-256
            |--------------------------------------------------------------------------
            */

            $checksum =
                hash_file(
                    'sha256',
                    $localPath
                );

            /*
            |--------------------------------------------------------------------------
            | 5. Upload to Supabase
            |--------------------------------------------------------------------------
            */

            $this->uploadToSupabase(
                $localPath,
                $storagePath
            );

            /*
            |--------------------------------------------------------------------------
            | 6. Update log
            |--------------------------------------------------------------------------
            */

            $backupLog->update([
                'file_size' =>
                    $fileSize,

                'checksum' =>
                    $checksum,

                'status' =>
                    'completed',

                'completed_at' =>
                    now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | 7. Delete old backups
            |--------------------------------------------------------------------------
            */

            $this->cleanupOldBackups();

            /*
            |--------------------------------------------------------------------------
            | 8. Delete local temporary file
            |--------------------------------------------------------------------------
            */

            $this->deleteLocalFile(
                $localPath
            );

            Log::info(
                'Database backup completed',
                [
                    'backup_id' =>
                        $backupLog->id,

                    'file_name' =>
                        $fileName,

                    'file_size' =>
                        $fileSize,

                    'checksum' =>
                        $checksum,
                ]
            );

            return $backupLog->fresh();

        } catch (Throwable $e) {

            $backupLog->update([
                'status' =>
                    'failed',

                'error_message' =>
                    $e->getMessage(),

                'completed_at' =>
                    now(),
            ]);

            $this->deleteLocalFile(
                $localPath
            );

            Log::error(
                'Database backup failed',
                [
                    'backup_id' =>
                        $backupLog->id,

                    'error' =>
                        $e->getMessage(),
                ]
            );

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Configuration
    |--------------------------------------------------------------------------
    */

    private function validateConfiguration(): void
    {
        $required = [
            'SUPABASE_URL',
            'SUPABASE_SERVICE_ROLE_KEY',
            'SUPABASE_BACKUPS_BUCKET',
            'BACKUP_DB_HOST',
            'BACKUP_DB_USERNAME',
            'BACKUP_DB_PASSWORD',
        ];

        foreach ($required as $key) {

            if (!env($key)) {

                throw new \RuntimeException(
                    "Missing backup configuration: {$key}"
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Upload
    |--------------------------------------------------------------------------
    */

    private function uploadToSupabase(
        string $localPath,
        string $storagePath
    ): void {

        $baseUrl =
            rtrim(
                config('services.supabase.url'),
                '/'
            );

        $bucket =
            trim(
                config(
                    'services.supabase.backups_bucket',
                    'backups'
                ),
                '/'
            );

        $key =
            config('services.supabase.key');

        $uploadUrl =
            $baseUrl .
            '/storage/v1/object/' .
            $bucket .
            '/' .
            $storagePath;

        $response = Http::timeout(
            (int) env(
                'BACKUP_UPLOAD_TIMEOUT',
                600
            )
        )
            ->withHeaders([
                'apikey' =>
                    $key,

                'Authorization' =>
                    'Bearer ' . $key,

                'Content-Type' =>
                    'application/octet-stream',
            ])
            ->withBody(
                file_get_contents($localPath),
                'application/octet-stream'
            )
            ->post($uploadUrl);

        if (!$response->successful()) {

            throw new \RuntimeException(
                'Supabase backup upload failed: ' .
                $response->body()
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Cleanup
    |--------------------------------------------------------------------------
    */

    private function cleanupOldBackups(): void
    {
        $retentionDays =
            max(
                1,
                (int) env(
                    'BACKUP_RETENTION_DAYS',
                    7
                )
            );

        $cutoff =
            now()->subDays(
                $retentionDays
            );

        $oldBackups =
            BackupLog::where(
                'status',
                'completed'
            )
                ->where(
                    'created_at',
                    '<',
                    $cutoff
                )
                ->get();

        foreach ($oldBackups as $backup) {

            try {

                if ($backup->storage_path) {

                    $this->deleteFromSupabase(
                        $backup->storage_path
                    );
                }

                $backup->delete();

            } catch (Throwable $e) {

                Log::warning(
                    'Failed to delete old backup',
                    [
                        'backup_id' =>
                            $backup->id,

                        'storage_path' =>
                            $backup->storage_path,

                        'error' =>
                            $e->getMessage(),
                    ]
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Delete From Supabase
    |--------------------------------------------------------------------------
    */

    private function deleteFromSupabase(
        string $storagePath
    ): void {

        $baseUrl =
            rtrim(
                config('services.supabase.url'),
                '/'
            );

        $bucket =
            trim(
                config(
                    'services.supabase.backups_bucket',
                    'backups'
                ),
                '/'
            );

        $key =
            config('services.supabase.key');

        $deleteUrl =
            $baseUrl .
            '/storage/v1/object/' .
            $bucket .
            '/' .
            $storagePath;

        $response = Http::timeout(120)
            ->withHeaders([
                'apikey' =>
                    $key,

                'Authorization' =>
                    'Bearer ' . $key,
            ])
            ->delete($deleteUrl);

        if (
            !$response->successful()
            && $response->status() !== 404
        ) {

            throw new \RuntimeException(
                'Supabase backup deletion failed: ' .
                $response->body()
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Local Cleanup
    |--------------------------------------------------------------------------
    */

    private function deleteLocalFile(
        string $path
    ): void {

        if (is_file($path)) {

            @unlink($path);
        }
    }
}
