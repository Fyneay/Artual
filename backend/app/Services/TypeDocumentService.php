<?php

namespace App\Services;

use App\Classes\DataManipulator;
use App\Models\TypeDocument;

class TypeDocumentService
{
    protected DataManipulator $dataManipulator;

    public function __construct(DataManipulator $dataManipulator)
    {
        $this->dataManipulator = $dataManipulator;
        $this->dataManipulator->model = 'App\Models\TypeDocument';
    }

    public function save(array $data): TypeDocument
    {
        $typeDocument = $this->dataManipulator->save($data);
        return $typeDocument;
    }

    public function update(array $data, TypeDocument $typeDocument): TypeDocument
    {
        return $this->dataManipulator->update($data, $typeDocument);
    }

    public function delete(TypeDocument $typeDocument): TypeDocument
    {
        return $this->dataManipulator->delete($typeDocument);
    }
}
