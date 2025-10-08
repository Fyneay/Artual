<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectionRequest extends FormRequest
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
            'name' => 'required|string',
            'user_id' => 'required|integer|exists:users,id',
            'type_id' => 'required|integer|exists:types_sections,id',
            'created_at' => 'nullable|date',
            'updated_at' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Имя раздела должно быть заполнено',
            'user_id.required' => 'Идентификатор пользователя должен быть заполнен',
            'user_id.exists' => 'Идентификатор пользователя несуществует',
            'type_id.required' => 'Идентификатор типа секции должен быть заполнен',
            'type_id.exists' => 'Идентификатор типа секции несуществует',
        ];
    }
}
