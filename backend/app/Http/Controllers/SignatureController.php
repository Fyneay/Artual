<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSignatureRequest;
use App\Repositories\SignatureRepository;
use App\Services\SignatureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\SignatureResource;
use App\Http\Resources\SignatureArchiveResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\SignatureArchive;
use Symfony\Component\HttpFoundation\StreamedResponse;
class SignatureController extends Controller
{
    public function __construct(
        private SignatureRepository $signatureRepository,
        private SignatureService $signatureService
    ) {}


    public function store(StoreSignatureRequest $request): SignatureResource
    {
        $validated = $request->validated();

        $signature = $this->signatureService->createSignature(
            signatureData: $validated['signature_data'],
            certificateName: $validated['certificate_name'],
            certificateSubject: $validated['certificate_subject'],
            userId: Auth::id()
        );

        if (isset($validated['article_id'])) {
            $this->signatureService->attachToArticle(
                $signature->id,
                $validated['article_id']
            );
        }

        return new SignatureResource($signature);
    }


    public function createArchive(Request $request): SignatureArchiveResource
    {
        $request->validate([
            'article_ids' => 'required|array',
            'article_ids.*' => 'integer|exists:articles,id',
        ]);

        $archive = $this->signatureService->createArchive(
            articleIds: $request->input('article_ids'),
            userId: Auth::id()
        );

        return new SignatureArchiveResource($archive);
    }

    public function show(int $id): SignatureResource
    {
    $signature = $this->signatureRepository->getOne($id);
    return new SignatureResource($signature);
    }

    public function showArchivesByArticle(int $articleId): ResourceCollection
    {
        $archives = $this->signatureService->getArchivesByArticle($articleId);
        return SignatureArchiveResource::collection($archives);
    }


public function getSigningData(int $articleId): JsonResponse
{
    $article = \App\Models\Article::with('files')->findOrFail($articleId);

    $approvedFiles = $article->files->where('status', 'approved');

    if ($approvedFiles->isEmpty()) {
        return response()->json([
            'error' => 'Нет файлов со статусом проверено для подписи'
        ], 400);
    }

    $fileContents = [];
    foreach ($approvedFiles as $file) {
        try {
            $content = Storage::disk('sftp')->get($file->path);
            $fileContents[] = [
                'id' => $file->id,
                'filename' => $file->filename,
                'content' => base64_encode($content),
                'hash' => hash('sha256', $content)
            ];
        } catch (\Exception $e) {
            return response()->json([
                'error' => "Ошибка при чтении файла {$file->filename}: {$e->getMessage()}"
            ], 500);
        }
    }

    $combinedContent = implode('', array_column($fileContents, 'hash'));
    $combinedHash = hash('sha256', $combinedContent);

    return response()->json([
        'data' => [
            'article_id' => $articleId,
            'files_count' => $approvedFiles->count(),
            'combined_hash' => $combinedHash,
            'files' => $fileContents
        ]
    ]);
}

    public function downloadArchive(int $archiveId): StreamedResponse
    {
        $archive = SignatureArchive::findOrFail($archiveId);

        if (!Storage::disk('sftp')->exists($archive->archive_path)) {
            abort(404, 'Архив не найден');
        }

        return Storage::disk('sftp')->download($archive->archive_path, $archive->archive_name);
    }
}
