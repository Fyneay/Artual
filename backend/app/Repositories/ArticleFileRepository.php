<?php

namespace App\Repositories;

use App\Models\ArticleFile;
use Illuminate\Database\Eloquent\Collection;

class ArticleFileRepository extends Repository
{
    public function getByArticle(int $articleId): Collection
    {
        return ArticleFile::query()->where('article_id', $articleId)->get();
    }

    public function findByPath(string $path): ?ArticleFile
    {
        return ArticleFile::query()
            ->where('path', $path)
            ->first();
    }
}