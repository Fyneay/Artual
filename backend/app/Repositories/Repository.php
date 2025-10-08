<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Contracts\RepositoryInterface;
abstract class Repository implements RepositoryInterface
{
    public string $model;

    public function __construct(?string $model=null)
    {
        $this->model = $model ?: self::guessModelClass();;
    }

    private static function guessModelClass(): string
    {
        return preg_replace('/(.+)\\\\Repositories\\\\(.+)Repository$/m', '$1\Models\\\$2', static::class);
    }

    public function create(array $data) : Model
    {
        return $this->model::query()->create($data);
    }
    
    public function getAll() :Collection
    {
        return $this->model::all();
    }

    public function findOne(int $id) :?Model
    {
        return $this->model::query()->find($id);
    }

    public function findOneBy(array $data) :?Model
    {
        return $this->model::query()->where($data)->first();
    }

    public function getOne(int $id) :Model
    {
        return $this->model::query()->findOrFail($id);
    }

    public function getOneBy(array $data) :Model
    {
        return $this->model::query()->where($data)->firstOrFail();
    }

    
    
}
