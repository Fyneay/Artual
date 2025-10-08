<?php

namespace App\Observers;

use App\Models\UserGroup;
use Illuminate\Support\Facades\Log;

class UserGroupObserver
{
    public function created(UserGroup $userGroup)
    {
        Log::info('Группа пользователей {name} создана',['name'=>$userGroup->name]);
    }
    public function updated(UserGroup $userGroup)
    {
        Log::info('Группа пользователей {name} обновлена',['name'=>$userGroup->name]);
    }
    public function deleted(UserGroup $userGroup)
    {
        Log::info('Группа пользователей {name} удалена',['name'=>$userGroup->name]);
    }
}
