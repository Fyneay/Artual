<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileCheckRequest;
use App\Jobs\FileCheckJob;
use App\Services\LocalFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\ArticleFile;
use App\Repositories\ArticleFileRepository;

class FileCheckController extends Controller
{
    private LocalFileUploader $fileUploader;

    public function __construct()
    {
        // Используем SFTP диск для загрузки в карантин
        $this->fileUploader = new LocalFileUploader('sftp');
    }

    public function uploadAndCheck(FileCheckRequest $request): JsonResponse
    {
        try {
            $file = $request->file('file');
            $articleId = $request->input('article_id');
            $metadata = $request->input('metadata', []);

            $quarantinePath = 'quarantine/pending';
            $uploadedPath = $this->fileUploader->upload($file, $quarantinePath);

            $articleFile = ArticleFile::create([
                'article_id' => $articleId,
                'filename' => $file->getClientOriginalName(),
                'path' => $uploadedPath,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'status' => 'pending',
                'threat_name' => null,
            ]);

            Log::info("ArticleFile создан", [
                'article_file_id' => $articleFile->id,
                'article_id' => $articleId,
                'quarantine_path' => $uploadedPath,
            ]);

            FileCheckJob::dispatch(
                fileId: $articleFile->id,
                quarantinePath: $uploadedPath,
                action: 'scan',
                metadata: array_merge($metadata, [
                    'article_id' => $articleId,
                    'article_file_id' => $articleFile->id,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_at' => now()->toIso8601String(),
                ])
            );

            return response()->json([
                'success' => true,
                'message' => 'Файл загружен в карантин и отправлен на проверку',
                'data' => [
                    'article_file_id' => $articleFile->id,
                    'article_id' => $articleId,
                    'quarantine_path' => $uploadedPath,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'status' => 'pending',
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error("Ошибка при загрузке файла в карантин", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при загрузке файла: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function checkStatus(string $path): JsonResponse
    {
        try {
            $exists = Storage::disk('sftp')->exists($path);

            if (!$exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Файл не найден в карантине',
                    'path' => $path,
                ], 404);
            }

            $size = Storage::disk('sftp')->size($path);
            $lastModified = Storage::disk('sftp')->lastModified($path);

            return response()->json([
                'success' => true,
                'data' => [
                    'path' => $path,
                    'exists' => true,
                    'size' => $size,
                    'last_modified' => date('Y-m-d H:i:s', $lastModified),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error("Ошибка при проверке статуса файла", [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при проверке статуса файла: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteFromQuarantine(string $path): JsonResponse
    {
        try {
            $deleted = Storage::disk('sftp')->delete($path);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Не удалось удалить файл из карантина',
                    'path' => $path,
                ], 500);
            }

            Log::info("Файл удален из карантина", ['path' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Файл успешно удален из карантина',
                'path' => $path,
            ]);

        } catch (\Exception $e) {
            Log::error("Ошибка при удалении файла из карантина", [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при удалении файла: ' . $e->getMessage(),
            ], 500);
        }
    }
}

