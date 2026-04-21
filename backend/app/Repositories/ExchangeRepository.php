<?php

namespace App\Repositories;

use App\Models\Exchange;
use Illuminate\Database\Eloquent\Collection;

class ExchangeRepository extends Repository
{
    public function getOneWithRelations(int $id, array $relations): Exchange
    {
        return Exchange::with($relations)->findOrFail($id);
    }

    public function getAllWithRelations(array $relations): Collection
    {
        return Exchange::with($relations)->get();
    }
    public function findByName(string $name): ?Exchange
    {
        return Exchange::query()->where('name', $name)->first();
    }

    public function getByCreator(int $userId): Collection
    {
        return Exchange::query()->where('created_by', $userId)->get();
    }
}
