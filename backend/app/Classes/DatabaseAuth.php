<?php

namespace App\Classes;

use App\Models\User;
use App\Contracts\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Repositories\UserRepository;


final class DatabaseAuth implements Auth
{
    private UserRepository $userRepository;
    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }
    public function login($data): array
    {
        $user = null;
        $identifier = null;

        if (isset($data['email']) && !empty($data['email'])) {
            $user = $this->userRepository->findOneByEmail($data['email']);
            $identifier = $data['email'];
        } elseif (isset($data['nickname']) && !empty($data['nickname'])) {
            $user = $this->userRepository->findOneByNickname($data['nickname']);
            $identifier = $data['nickname'];
        }

        if(!$user || !Hash::check($data['password'], $user->password))
        {
            throw ValidationException::withMessages([
                'email' => ['Данной почты несуществует или значение введенно некорректно'],
                'nickname' => ['Данного никнейма несуществует или значение введенно некорректно'],
            ]);
        }

        $token = $user->createToken($identifier ?: $user->email)->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function register($data) : array
    {
        $user = $this->userRepository->create([
            'nickname' => $data['nickname'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id'=> $data['role_id']
        ]);
        $token = $user->createToken($data['email'])->plainTextToken;
        return ['user' => $user, 'token' => $token];
    }

    public function logout($data): void
    {
        $data->tokens()->delete();
    }

}
