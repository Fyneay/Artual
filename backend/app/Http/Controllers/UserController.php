<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Auth;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Repositories\UserRepository;
use App\Services\UserService;
class UserController extends Controller
{
    protected Auth $authService;
    protected UserService $userService;
    protected UserRepository $userRepository;
    public function __construct(Auth $authService, UserRepository $userRepository, UserService $userService)
    {
         $this->authService = $authService;
         $this->userService = $userService;
         $this->userRepository = $userRepository;
    }

    public function index(): ResourceCollection
    {
        $user = $this->userRepository->getAll();
        return UserResource::collection($user);
    }

    public function show(int $id): UserResource
    {
            $user = $this->userRepository->getOne($id);
            return new UserResource($user);
    }


    public function store(StoreUserRequest $request): UserResource
    {
            $validated = $request->validated();
            $user=$this->authService->register($validated);
            return new UserResource($user);
    }

    public function update(StoreUserRequest $request, int $id): UserResource
    {
            $validated = $request->validated();
            $user = $this->userService->update($id, $validated);
            return new UserResource($user);
    }

    public function destroy(int $id): UserResource
    {
            $user = User::query()->findOrFail($id);
            $user->delete();
            return new UserResource($user);
    }

}
