<?php

namespace App\Repositories;

use App\Models\Access;
use Illuminate\Database\Eloquent\Collection;

class AccessRepository extends Repository
{

    public function getOneWithRelations(int $id, array $relations): Access
    {
        return Access::with($relations)->findOrFail($id);
    }


    public function getAllWithRelations(array $relations): Collection
    {
        return Access::with($relations)->get();
    }


    public function getByArticle(int $articleId): Collection
    {
        return Access::query()->where('article_id', $articleId)->get();
    }

    public function getByGrantedTo(int $userId): Collection
    {
        return Access::query()->where('granted_by', $userId)->get();
    }

    public function getByCreator(int $userId): Collection
    {
        return Access::query()->where('created_by', $userId)->get();
    }
}

