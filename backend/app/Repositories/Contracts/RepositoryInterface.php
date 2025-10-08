<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    public function getAll() :Collection;
    public function findOne(int $id) :?Model;
    public function findOneBy(array $data) :?Model;
    public function getOne(int $id) :Model;
    public function getOneBy(array $data) :Model;

}
