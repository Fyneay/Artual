<?php

namespace App\Jobs;

use App\Services\RabbitMQService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(
        public int $fileId,
        public string $quarantinePath,
        public string $action = 'scan',
        public ?array $metadata = null
    ) {}

    public function handle(RabbitMQService $rabbitMQ): void
    {
        try {
            if (!Storage::disk('sftp')->exists($this->quarantinePath)) {
                throw new \Exception("Файл не найден в карантине SFTP: {$this->quarantinePath}");
            }

            $fullPath = env('SFTP_ROOT', '/storage') . '/' . $this->quarantinePath;

            $message = [
                'id' => uniqid('file_check_', true),
                'type' => 'file_check',
                'timestamp' => now()->toIso8601String(),
                'payload' => [
                    'file_id' => $this->fileId,
                    'file_path' => $this->quarantinePath,
                    'full_path' => $fullPath,
                    'storage_disk' => 'sftp',
                    'sftp_config' => [
                        'host' => env('SFTP_HOST', 'localhost'),
                        'port' => (int)env('SFTP_PORT', 22),
                        'username' => env('SFTP_USER', 'root'),
                        'password' => env('SFTP_PASSWORD', 'admin'),
                        'root' => env('SFTP_ROOT', '/storage'),
                    ],
                    'action' => $this->action,
                    'metadata' => $this->metadata ?? [],
                ],
                'callback_queue' => 'file.check.response',
                'correlation_id' => $this->job->getJobId(),
            ];

            $rabbitMQ->publish(
                queue: 'file.check.request',
                data: $message,
                durable: true
            );

            Log::info("Файл проверки отправлен в очередь", [
                'file_id' => $this->fileId,
                'quarantine_path' => $this->quarantinePath,
                'full_path' => $fullPath,
            ]);
        } catch (\Exception $e) {
            Log::error("Ошибка при отправке файла проверки", [
                'file_id' => $this->fileId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        try {
            if (Storage::disk('sftp')->exists($this->quarantinePath)) {
                Storage::disk('sftp')->delete($this->quarantinePath);
                Log::warning("Файл удален из карантина SFTP после ошибки", [
                    'file_id' => $this->fileId,
                    'quarantine_path' => $this->quarantinePath,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Ошибка при удалении файла из карантина SFTP", [
                'file_id' => $this->fileId,
                'error' => $e->getMessage(),
            ]);
        }

        Log::error("Файл проверки не выполнен", [
            'file_id' => $this->fileId,
            'error' => $exception->getMessage(),
        ]);
    }
}
