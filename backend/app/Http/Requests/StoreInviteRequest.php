<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInviteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
            'user_id' => 'required|integer|exists:users,id',
            'user_role_id' => 'required|integer|exists:users_groups,id',
            'created_at' => 'required|date',
            'expires_at' => 'required|date|after:now',
            'ttl' => 'nullable|integer|min:60|max:86400',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email адрес обязателен',
            'email.email' => 'Некорректный формат email адреса',
            'user_id.required' => 'ID пользователя обязателен',
            'user_id.exists' => 'Пользователь не найден',
            'user_role_id.required' => 'ID роли пользователя обязателен',
            'user_role_id.exists' => 'Роль пользователя не найдена',
            'expires_at.required' => 'Дата истечения обязательна',
            'created_at.required' => 'Дата создания обязательна',
            'created_at.date' => 'Дата создания должна быть в формате даты',
            'expires_at.after' => 'Дата истечения должна быть в будущем',
            'ttl.min' => 'TTL должен быть не менее 60 секунд',
            'ttl.max' => 'TTL не должен превышать 24 часа',
        ];
    }
}
