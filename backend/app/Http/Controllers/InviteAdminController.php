<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UuidInviteService;
use App\Services\Auth;
use App\Values\Invites\InviteDTO;
use App\Http\Requests\StoreInviteRequest;
use App\Http\Requests\AcceptInviteRequest;
use App\Http\Resources\InviteResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\JsonResponse;

class InviteAdminController extends Controller
{
    protected readonly UuidInviteService $uuidInviteService;
    protected readonly Auth $authService;

    public function __construct(UuidInviteService $uuidInviteService, Auth $authService)
    {
        $this->uuidInviteService = $uuidInviteService;
        $this->authService = $authService;
    }

    public function viewAll(): ResourceCollection
    {
        $invites = [];
        foreach ($this->uuidInviteService->getAll() as $key => $inviteData) {
            $inviteDTO = InviteDTO::fromJson($inviteData);
            $invites[] = $inviteDTO;
        }
        return InviteResource::collection($invites);
    }

    public function view(string $key): InviteResource
    {
        $invite = $this->uuidInviteService->get($key);
        $inviteDTO = InviteDTO::fromJson($invite);
        return InviteResource::make($inviteDTO);
    }

    public function store(StoreInviteRequest $request): JsonResponse
    {
        $key = $this->uuidInviteService->generate();
        $inviteDTO = InviteDTO::make(            
            $key,
            $request->validated()['email'],
            now()->format('Y-m-d H:i:s'),
            $request->validated()['expires_at'],
            $request->validated()['user_id'],
            $request->validated()['user_role_id'],
            0
        );

        $ttl = $request->validated()['ttl'] ?? 3600;
        $created = $this->uuidInviteService->create($key, $inviteDTO, $ttl);

        if (!$created) {
            return response()->json([
                'error' => 'Не удалось создать приглашение'
            ], 500);
        }

        return response()->json([
            'message' => 'Приглашение успешно создано',
            'data' => [
                'key' => $key,
                'invite_url' => url("/invites/{$key}"),
                'expires_at' => $request->validated()['expires_at']
            ]
        ], 201);
    }

    public function accept(string $key, AcceptInviteRequest $request): JsonResponse
    {
        if (!$this->uuidInviteService->checkExists($key)) {
            return response()->json(['error' => 'Приглашение не найдено'], 404);
        }

        if ($this->uuidInviteService->checkUsed($key)) {
            return response()->json(['error' => 'Приглашение уже использовано'], 400);
        }

        if ($this->uuidInviteService->checkDate($key)) {
            return response()->json(['error' => 'Приглашение истекло'], 400);
        }

        $inviteData = $this->uuidInviteService->get($key);
        $inviteDTO = InviteDTO::fromJson($inviteData);

        $userData = [
            'email' => $inviteDTO->email,
            'password' => $request->input('password'),
            'nickname' => $request->input('nickname'),
            'role_id' => $inviteDTO->user_role_id
        ];

        try {
            $result = $this->authService->register($userData);
            
            $usedInviteDTO = InviteDTO::make(
                $key,
                $inviteDTO->email,
                $inviteDTO->created_by,
                $inviteDTO->created_at,
                $inviteDTO->expires_at,
                $inviteDTO->user_id,
                $inviteDTO->user_role_id,
                1
            );
            
            $this->uuidInviteService->create($key, $usedInviteDTO, 3600);

            return response()->json([
                'message' => 'Пользователь успешно зарегистрирован',
                'data' => $result
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка при регистрации пользователя: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete(string $key): JsonResponse
    {
        $deleted = $this->uuidInviteService->delete($key);
        
        if (!$deleted) {
            return response()->json(['error' => 'Не удалось удалить приглашение'], 500);
        }

        return response()->json(['message' => 'Приглашение успешно удалено'], 200);
    }
}
