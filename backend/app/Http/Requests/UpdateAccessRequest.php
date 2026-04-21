<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccessRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

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
            'name' => 'sometimes|string|max:255',
            'granted_by' => 'sometimes|integer|exists:users,id',
            'article_id' => 'sometimes|integer|exists:articles,id',
            'access_date' => 'sometimes|date',
            'close_date' => 'nullable|date|after_or_equal:access_date',
            'reason' => 'nullable|string|max:1000',
            'status_id' => 'nullable|integer|exists:status,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.max' => 'Название допуска не должно превышать 255 символов',
            'granted_by.exists' => 'Пользователь не существует в базе данных',
            'article_id.exists' => 'Дело не существует в базе данных',
            'access_date.date' => 'Дата выдачи должна быть в формате даты',
            'close_date.date' => 'Дата закрытия должна быть в формате даты',
            'close_date.after_or_equal' => 'Дата закрытия должна быть не раньше даты выдачи',
            'reason.max' => 'Причина не должна превышать 1000 символов',
            'status_id.exists' => 'Статус не существует в базе данных',
        ];
    }
}

