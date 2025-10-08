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
        //$user = User::query()->where('email', $data['email'])->first();
        $user = $this->userRepository->findOneByEmail($data['email']);

        if(!$user || !Hash::check($data['password'], $user->password))
        {
            throw ValidationException::withMessages([
                'email' => ['Данной почты несуществует или значение введенно некорректно'],
            ]);
        }
        $token = $user->createToken($data['email'])->plainTextToken;
        //$token = $user->createToken($data['email'])->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function register($data) : array
    {
        // $user = User::query()->create([
        //     'nickname' => $data['nickname'],
        //     'email' => $data['email'],
        //     'password' => Hash::make($data['password']),
        //     'role_id'=> $data['role_id']
        // ]);
        $user = $this->userRepository->create([
            'nickname' => $data['nickname'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id'=> $data['role_id']
        ]);
        //$token = $user->createToken($data['email'])->plainTextToken;
        $token = $user->createToken($data['email'])->plainTextToken;
        return ['user' => $user, 'token' => $token];
    }

    public function logout($data): void
    {
        $data->tokens()->delete();
    }

}