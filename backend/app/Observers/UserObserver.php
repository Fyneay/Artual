<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    public function created(User $user) : void
    {
        Log::info("Пользовать {name} был создан", ['name' => $user->nickname, 'email' => $user->email]);
    }

    public function updated(User $user) : void
    {
        Log::info("Пользовать {name} был изменен", ['name' => $user->nickname, 'email' => $user->email]);
    }

    public function deleted(User $user) : void
    {
        Log::info("Пользовать {name} был удален", ['name' => $user->nickname]);
    }
}
