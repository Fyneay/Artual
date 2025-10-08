<?php

namespace App\Repositories;

use App\Models\User;
class UserRepository extends Repository
{
    public function findOneByEmail(string $email): ?User
    {
        return User::query()->firstWhere('email', $email);
    }

    public function findOneByNickname(string $nickname): ?User
    {
        return User::query()->firstWhere('nickname', $nickname);
    }

}