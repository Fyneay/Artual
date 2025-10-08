<?php
namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
class UserService
{
    private UserRepository $userRepository;
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function update(int $id, array $data): ?User
    {
        $user = $this->userRepository->getOne($id);
        if (!$user) {
            return null;
        }
        $user->fill([
            'nickname' => $data['nickname'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id'=> $data['role_id']
        ]);
        $user->save();
        return $user;
    }
    // public function delete(int $id): User
    // {
    //     $user->delete();
    //     return $user;
    // }
    
}
