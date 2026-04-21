<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(
        private Auth $authService
    ) {}

    /**
     * Авторизация пользователя
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        try {
            $result = $this->authService->login($validated);
            
            return response()->json([
                'user' => [
                    'id' => $result['user']->id,
                    'email' => $result['user']->email,
                    'nickname' => $result['user']->nickname,
                ],
                'token' => $result['token'],
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'Ошибка при входе в систему',
            ], 401);
        }
    }

    /**
     * Выход пользователя
     */
    public function logout(): JsonResponse
    {
        try {
            $user = auth()->user();
            
            if ($user) {
                $this->authService->logout($user);
            }
            
            return response()->json([
                'message' => 'Успешный выход из системы',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ошибка при выходе из системы',
            ], 500);
        }
    }
}

