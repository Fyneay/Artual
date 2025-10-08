<?php

namespace App\Classes;

use Illuminate\Database\Eloquent\Model;

final class DataManipulator
{
    public ?string $model=null;

    public function __construct(?string $model=null)
    {
        $this->model = $model;
    }

    public function save(array $data) : Model
    {
        return $this->model::create($data);
    }

    public function update(array $data, Model $model) : Model
    {
        $model->update($data);
        return $model;
    }

    public function delete(Model $model) : Model
    {
        $model->delete();
        return $model;  
    }
    
}