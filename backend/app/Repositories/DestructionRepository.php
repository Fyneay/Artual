<?php

namespace App\Repositories;

use App\Models\Destruction;
use Illuminate\Database\Eloquent\Collection;

class DestructionRepository extends Repository
{
    public function getOneWithRelations(int $id, array $relations): Destruction
    {
        return Destruction::with($relations)->findOrFail($id);
    }

    public function getAllWithRelations(array $relations): Collection
    {
        return Destruction::with($relations)->get();
    }

    public function findByName(string $name): ?Destruction
    {
        return Destruction::query()->where('name', $name)->first();
    }

    public function getByCreator(int $userId): Collection
    {
        return Destruction::query()->where('created_by', $userId)->get();
    }
}
