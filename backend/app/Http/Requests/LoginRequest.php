<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'email' => 'nullable|string|email|required_without:nickname',
            'nickname' => 'nullable|string|min:3|required_without:email',
            'password' => 'nullable|string|min:6|required',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.email' => 'Поле email должно быть действительным адресом электронной почты.',
            'email.required_without' => 'Необходимо указать email или никнейм.',
            'nickname.min' => 'Никнейм должен содержать минимум :min символов.',
            'nickname.required_without' => 'Необходимо указать email или никнейм.',
            'password.required' => 'Поле пароль обязательно для заполнения.',
            'password.min' => 'Пароль должен содержать минимум :min символов.',
        ];
    }
}

