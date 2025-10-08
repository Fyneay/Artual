<?php

namespace App\Services;

use App\Services\LdapSearch;
use App\Exceptions\LdapException;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Classes\Ldap\LdapAuth as LdapAuthClass;
use Illuminate\Support\Facades\Hash;

class LdapAuth
{
    private LdapSearch $ldapSearch;

    public function __construct(LdapSearch $ldapSearch)
    {
        $this->ldapSearch = $ldapSearch;
    }

    public function auth($data)
    {
        try {
            $user = $this->ldapSearch->searchUser($data['username']);
            //authentication (maybe other service)
            if ($user['count'] != 0) {
            }

        } catch (LdapException $e) {
            throw new LdapException('Пользователь не найден');
        }

        if (!$this->checkPassword($user, $data)) {
            throw new LdapException('Неверный пароль');
        }

        return $user;
    }

    private function checkPassword($user, $data): bool
    {
        return false;
    }


}
