<?php

namespace App\Jobs;

use App\Services\LocalFileUploader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\ArticleFile;

class ProcessFileCheckResultJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $articleFileId,
        public string $quarantinePath,
        public bool $isSafe,
        public ?string $threatName = null,
        public ?string $error = null
    ) {}

    public function handle(): void
    {
        try {
            $articleFile = ArticleFile::findOrFail($this->articleFileId);
            $sftpDisk = Storage::disk('sftp');

            if ($this->isSafe) {
                $permanentPath = 'files/' . basename($this->quarantinePath);

                if ($sftpDisk->exists($this->quarantinePath)) {
                    $content = $sftpDisk->get($this->quarantinePath);
                    $sftpDisk->put($permanentPath, $content);
                    $sftpDisk->delete($this->quarantinePath);

                    $articleFile->update([
                        'path' => $permanentPath,
                        'status' => 'approved',
                        'threat_name' => null,
                    ]);

                    Log::info("Файл одобрен и перемещен", [
                        'article_file_id' => $this->articleFileId,
                        'from' => $this->quarantinePath,
                        'to' => $permanentPath,
                    ]);
                }
            } else {
                if ($sftpDisk->exists($this->quarantinePath)) {
                    $sftpDisk->delete($this->quarantinePath);
                }

                $articleFile->update([
                    'status' => 'rejected',
                    'threat_name' => $this->threatName,
                ]);

                Log::warning("Файл отклонен", [
                    'article_file_id' => $this->articleFileId,
                    'threat' => $this->threatName,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Ошибка при обработке результата проверки", [
                'article_file_id' => $this->articleFileId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
