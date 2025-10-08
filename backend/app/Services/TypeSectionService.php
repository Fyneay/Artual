<?php

namespace App\Services;

use App\Classes\DataManipulator;
use App\Models\TypeSection;

class TypeSectionService
{
    protected DataManipulator $dataManipulator;

    public function __construct(DataManipulator $dataManipulator)
    {
        $this->dataManipulator = $dataManipulator;
        $this->dataManipulator->model='App\Models\TypeSection';
    }

    public function save(array $data): TypeSection
    {
        $typeSection = $this->dataManipulator->save($data);
        return $typeSection;
    }

    public function update(array $data, TypeSection $typeSection): TypeSection
    {
        return $this->dataManipulator->update($data, $typeSection);

    }

    public function delete(TypeSection $typeSection): TypeSection
    {
        return $this->dataManipulator->delete($typeSection); 
    }
}