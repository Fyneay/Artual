<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Http\Requests\StoreArticleRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Repositories\ArticleRepository;
use App\Services\ArticleService;

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
        $articles = $this->articleRepository->getAll();

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
        $article = $this->articleRepository->getOne($id);

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
}
