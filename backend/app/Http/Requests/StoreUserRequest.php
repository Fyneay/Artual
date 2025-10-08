<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

    protected $stopOnFirstFailure=true;

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
            'nickname'=>'required|string|between:2,100',
            'email'=>'required|string|email|max:100|unique:users',
            'password'=>['required',Password::min(6)],
            'role_id'=>'required|exists:users_groups,id',
        ];
    }


    /**
     * Получить сообщения об ошибках для определенных правил валидации.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nickname.required' => 'поле никнейм должно быть обязательно заполнено',
            'email.required' => 'поле почта должно быть обязательно заполнено',
            'email.email'=>'поле почта должно быть правильного формата',
            'password.required' => 'поле пароль должно быть обязательно заполнено',
            'role_id.required'=> 'значение роли не может быть пустым'
        ];
    }
}
