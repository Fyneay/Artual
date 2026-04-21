<?php

namespace App\Console\Commands;

use App\Jobs\ProcessFileCheckResultJob;
use App\Services\RabbitMQService;
use Illuminate\Console\Command;

class ConsumeFileCheckResults extends Command
{
    protected $signature = 'rabbitmq:consume-file-check-results';
    protected $description = 'Подписка в результаты очереди из RabbitMQ';

    public function handle(RabbitMQService $rabbitMQ): void
    {
        $this->info('Запуск потребления результатов проверки файлов...');

        $rabbitMQ->consume('file.check.response', function ($data, $msg) {
            $this->info('Получен результат проверки файла: ' . $data['payload']['file_path']);

            ProcessFileCheckResultJob::dispatch(
                articleFileId: $data['payload']['file_id'],
                quarantinePath: $data['payload']['file_path'],
                isSafe: $data['result']['is_safe'] ?? false,
                threatName: $data['result']['threat_name'] ?? null,
                error: $data['error'] ?? null
            );
        });
    }
}
