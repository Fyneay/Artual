<?php

namespace App\Classes\Ldap;

use App\Contracts\Auth;
use App\Services\LdapSearch;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\LdapException;
use Illuminate\Validation\ValidationException;
use App\Repositories\UserRepository;

class LdapAuth implements Auth
{
    private LdapSearch $ldapSearch;
    private UserRepository $userRepository;
    public function __construct(LdapSearch $ldapSearch, UserRepository $userRepository)
    {
        $this->ldapSearch = $ldapSearch;
        $this->userRepository = $userRepository;
    }

    public function login($data): array
    {
        $this->validateData($data);
        try {
            $user = $this->ldapSearch->searchUser($data['nickname']);
        } catch (LdapException $e) {
            throw new LdapException('Пользователь не найден в домене');
        }
        if (!$this->checkPassword($user, $data)) {
            throw new LdapException('Неверный пароль');
        }

        $userDB = $this->userRepository->findOneByNickname($user['nickname']);
        return $this->createAuthResponse($userDB, $data['nickname']);
    }


    public function register($data): array
    {
        $this->validateData($data);
        try {
            $user = $this->ldapSearch->searchUser($data['nickname']);
        } catch (LdapException $e) {
            throw new LdapException('Пользователь не найден в домене');
        }
        if ($this->checkUser($user)) {
            throw new LdapException('Пользователь уже зарегистрирован в каталоге');
        }
        if (!$this->checkPassword($user, $data)) {
            throw new LdapException('Неверный пароль');
        }
        //Внести запись в БД через репозиторий
        // $user = User::query()->create([
        //     'nickname' => $user['nickname'],
        //     'password' => Hash::make($user['password']),
        //     'role_id'=> $user['gidNumber'],
        //     'email' => $user['email']
        // ]);
        $user = $this->userRepository->create([
            'nickname' => $data['nickname'],
            'password' => Hash::make($data['password']),
            'role_id'=> $data['role_id'],
            'email' => $data['email']
        ]);
        return $this->createAuthResponse($user, $data['nickname']);
    }

    public function logout($data): void
    {
            $data->tokens()->delete();
    }

    private function createAuthResponse(User $user, $nickname): array
    {
        $token = $user->createToken($nickname)->plainTextToken;
        return ['user' => $user, 'token' => $token];
    }

    private function checkPassword($user, $data): bool
    {
        return $user['password'] == $data['password'];
    }

    private function checkUser($user) : bool
    {
        return $this->userRepository->findOneByNickname($user['nickname']) != null;
    }

    private function validateData($data): void
    {
        if (!isset($data['nickname']) || !isset($data['password'])) {
            throw ValidationException::withMessages(['error'=> 'Неверный логин или пароль']);
        }
    }
}
