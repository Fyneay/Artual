<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleResource;
use App\Http\Resources\SectionStatisticsResource;
use App\Models\Article;
use App\Http\Requests\StoreArticleRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Repositories\ArticleRepository;
use App\Services\ArticleService;
use App\Models\ArticleFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArticleController extends Controller
    {
    protected readonly ArticleRepository $articleRepository;
    protected ArticleService $articleService;

    public function __construct(ArticleRepository $articleRepository, ArticleService $articleService)
    {
        $this->articleRepository = $articleRepository;
        $this->articleService = $articleService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): ResourceCollection
    {
        $articles = $this->articleRepository->getAll()->load(['user', 'files', 'listPeriod']);

        return ArticleResource::collection($articles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(storeArticleRequest $request): ArticleResource
    {
        $validated = $request->validated();
        $article = $this->articleService->save($validated);
        return new ArticleResource($article);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): ArticleResource
    {
        $article = $this->articleRepository->getOne($id)->load(['user', 'files', 'listPeriod']);

        return new ArticleResource($article);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(storeArticleRequest $request, string $id): ArticleResource
    {
        $article = $this->articleRepository->getOne($id);
        $validated = $request->validated();
        $articleUpdated = $this->articleService->update($validated, $article);

        return new ArticleResource($articleUpdated);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): ArticleResource
    {
        $article = $this->articleRepository->getOne($id);
        $articleDeleted = $this->articleService->delete($article);

        return new ArticleResource($articleDeleted);

    }

    public function getBySection(string $sectionId): ResourceCollection
    {
        $articles = $this->articleRepository->getBySection(intval($sectionId));
        return ArticleResource::collection($articles);
    }

    /**
     * Получить статьи, готовые к уничтожению
     */
    public function getReadyForDestruction(): ResourceCollection
    {
        $articles = $this->articleRepository->getReadyForDestruction()->load(['user', 'files', 'listPeriod', 'status']);
        return ArticleResource::collection($articles);
    }

    /**
     * Скачать файл статьи
     */
    public function downloadFile(int $fileId): StreamedResponse
    {
        $file = ArticleFile::findOrFail($fileId);

        if (!Storage::disk('sftp')->exists($file->path)) {
            abort(404, 'Файл не найден');
        }

        return Storage::disk('sftp')->download($file->path, $file->filename);
    }

    /**
     * Получить статистику по разделу
     */
    public function getSectionStatistics(string $sectionId): SectionStatisticsResource
    {
        $statistics = $this->articleRepository->getSectionStatistics(intval($sectionId));
        return new SectionStatisticsResource($statistics);
    }
}
