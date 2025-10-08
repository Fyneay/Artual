<?php

namespace App\Services;

use App\Classes\DataManipulator;
use App\Models\Section;

class SectionService
{
    protected DataManipulator $dataManipulator;

    public function __construct(DataManipulator $dataManipulator)
    {
        $this->dataManipulator=$dataManipulator;
        $this->dataManipulator->model = 'App\Models\Section';
    }

    public function save(array $data) : Section
    {
        $section = $this->dataManipulator->save($data);
        return $section;
    }

    public function update(array $data, Section $section) : Section
    {
        return $this->dataManipulator->update($data,$section);
    }

    public function delete(Section $section): Section
    {
        return $this->dataManipulator->delete($section);
    }

}