<?php

namespace App\Repositories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Collection;
class ArticleRepository extends Repository
{
    public function findPathFile(string $path) : ?Article
    {
        return Article::query()->where('path_file', $path)->first();
    }

    //Для eager loading
    public function getOneWithRelations(int $id, array $relations): Article
    {
        return Article::with($relations)->findOrFail($id);
    }

    public function getBySection(int $sectionId): Collection
    {
        return Article::query()->where('section_id', $sectionId)->with('status')->get();
    }

    public function getReadyForDestruction(): Collection
    {
        $readyForDestructionStatus = \App\Models\Status::where('name', 'выделен к уничтожению')->first();

        $articles = Article::query()
            ->where(function ($query) use ($readyForDestructionStatus) {
                if ($readyForDestructionStatus) {
                    $query->where('status_id', $readyForDestructionStatus->id);
                }
            })
            ->with(['status', 'listPeriod', 'user', 'files'])
            ->get();

        $articlesWithExpiredDate = Article::query()
            ->whereNotNull('list_period_id')
            ->whereNotNull('created_at')
            ->with(['status', 'listPeriod', 'user', 'files'])
            ->get()
            ->filter(function ($article) {
                $expirationDate = $article->expiration_date;
                if ($expirationDate === null) {
                    return false;
                }
                return $expirationDate->isPast();
            });

        $allReadyArticles = $articles->merge($articlesWithExpiredDate)->unique('id');

        return $allReadyArticles->values();
    }

    public function getSectionStatistics(int $sectionId): array
    {
        $articles = Article::query()
            ->where('section_id', $sectionId)
            ->with('files')
            ->get();

        $totalDocuments = $articles->count();

        $articleIds = $articles->pluck('id')->toArray();

        $totalAccesses = \App\Models\Access::query()
            ->whereIn('article_id', $articleIds)
            ->count();

        $totalWeight = 0;
        foreach ($articles as $article) {
            foreach ($article->files as $file) {
                $totalWeight += $file->file_size ?? 0;
            }
        }

        return [
            'total_documents' => $totalDocuments,
            'total_accesses' => $totalAccesses,
            'total_weight' => $totalWeight, // в байтах
        ];
    }
}
