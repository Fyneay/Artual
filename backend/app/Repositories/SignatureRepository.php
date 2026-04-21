<?php

namespace App\Repositories;

use App\Models\Signature;

class SignatureRepository extends Repository
{
    public function findByHash(string $hash): ?Signature
    {
        return Signature::query()->where('signature_hash', $hash)->first();
    }

    public function getByUser(int $userId)
    {
        return Signature::query()->where('signed_by', $userId)->get();
    }

    public function getByArticle(int $articleId)
    {
        return Signature::query()
            ->whereHas('articles', function ($query) use ($articleId) {
                $query->where('articles.id', $articleId);
            })
            ->get();
    }
}