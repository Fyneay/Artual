<?php

namespace App\Services;

use App\Classes\DataManipulator;
use App\Models\ListPeriod;

class ListPeriodService
{
    protected DataManipulator $dataManipulator;

    public function __construct(DataManipulator $dataManipulator)
    {
        $this->dataManipulator = $dataManipulator;
        $this->dataManipulator->model = 'App\Models\ListPeriod';
    }

    public function save(array $data): ListPeriod
    {
        $listPeriod = $this->dataManipulator->save($data);
        return $listPeriod;
    }

    public function update(array $data, ListPeriod $listPeriod): ListPeriod
    {
        return $this->dataManipulator->update($data, $listPeriod);
    }

    public function delete(ListPeriod $listPeriod): ListPeriod
    {
        return $this->dataManipulator->delete($listPeriod);
    }
}
