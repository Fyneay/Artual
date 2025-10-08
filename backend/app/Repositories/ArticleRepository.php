<?php

namespace App\Repositories;

use App\Models\Article;
class ArticleRepository extends Repository
{
    public function findPathFile(string $path) : ?Article
    {
        return Article::query()->where('path_file', $path)->first();
    }
}