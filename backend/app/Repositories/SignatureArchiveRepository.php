<?php

namespace App\Repositories;

use App\Models\SignatureArchive;

class SignatureArchiveRepository extends Repository
{
    public function getByArticle(int $articleId)
    {
        return SignatureArchive::query()
            ->where('article_id', $articleId)
            ->get();
    }

    public function getByCreator(int $userId)
    {
        return SignatureArchive::query()
            ->where('created_by', $userId)
            ->get();
    }

    public function findByPath(string $path): ?SignatureArchive
    {
        return SignatureArchive::query()
            ->where('archive_path', $path)
            ->first();
    }
}