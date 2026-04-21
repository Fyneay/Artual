<?php

namespace App\Services;

use App\Models\Signature;
use App\Models\SignatureArchive;
use App\Repositories\SignatureRepository;
use App\Repositories\ArticleRepository;
use Illuminate\Support\Facades\Storage;
use App\Repositories\SignatureArchiveRepository;
use Illuminate\Support\Str;
use ZipArchive;

class SignatureService
{
    public function __construct(
        private SignatureRepository $signatureRepository,
        private SignatureArchiveRepository $signatureArchiveRepository,
        private ArticleRepository $articleRepository
    ) {}

    public function createSignature(
        string $signatureData,
        string $certificateName,
        string $certificateSubject,
        int $userId
    ): Signature {
        $hash = hash('sha256', $signatureData);

        // Проверяем уникальность
        $existing = $this->signatureRepository->findByHash($hash);
        if ($existing) {
            return $existing;
        }

        return $this->signatureRepository->create([
            'signature_data' => $signatureData,
            'certificate_name' => $certificateName,
            'certificate_subject' => $certificateSubject,
            'signature_hash' => $hash,
            'signed_by' => $userId,
        ]);
    }

    public function attachToArticle(int $signatureId, int $articleId): void
    {
        $signature = $this->signatureRepository->getOne($signatureId);
        $signature->articles()->attach($articleId, [
            'signed_at' => now(),
        ]);
    }

    public function getArchivesByArticle(int $articleId)
    {
        return $this->signatureArchiveRepository->getByArticle($articleId);
    }

    public function createArchive(array $articleIds, int $userId): SignatureArchive
    {
        $archiveName = 'signature_archive_' . now()->format('Y-m-d_His') . '.zip';
        $tempPath = storage_path('app/temp/' . $archiveName);

        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($tempPath, ZipArchive::CREATE) !== TRUE) {
            throw new \Exception('Не удалось создать архив');
        }

        foreach ($articleIds as $articleId) {
            $article = $this->articleRepository->getOneWithRelations($articleId, ['files', 'signatures']);

            foreach ($article->files as $file) {
                $fileContent = Storage::disk('sftp')->get($file->path);
                $zip->addFromString("documents/{$file->filename}", $fileContent);
            }

            foreach ($article->signatures as $signature) {
                $zip->addFromString(
                    "signatures/{$signature->id}.sig",
                    $signature->signature_data
                );
            }
        }

        $zip->close();

        $archivePath = 'archives/' . $archiveName;
        Storage::disk('sftp')->put(
            $archivePath,
            file_get_contents($tempPath)
        );

        unlink($tempPath);

        $firstArticleId = $articleIds[0] ?? null;

        return $this->signatureArchiveRepository->create([
            'archive_name' => $archiveName,
            'archive_path' => $archivePath,
            'article_id' => $firstArticleId,
            'created_by' => $userId,
        ]);
    }
}
