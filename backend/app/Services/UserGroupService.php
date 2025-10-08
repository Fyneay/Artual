<?php

namespace App\Services;

use App\Classes\DataManipulator;
use App\Models\UserGroup;
class UserGroupService
{
    protected DataManipulator $dataManipulator;

    public function __construct(DataManipulator $dataManipulator)
    {
        $this->dataManipulator = $dataManipulator;
        $this->dataManipulator->model='App\Models\UserGroup';
    }

    public function save(array $data) : UserGroup
    {
        return $this->dataManipulator->save($data);
    }

    public function update(array $data, UserGroup $userGroup) : UserGroup
    {
        return $this->dataManipulator->update($data,$userGroup);
    }

    public function delete(UserGroup $userGroup) : UserGroup
    {
        return $this->dataManipulator->delete($userGroup);
    }
}