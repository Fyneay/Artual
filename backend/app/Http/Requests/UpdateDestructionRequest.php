<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDestructionRequest extends FormRequest
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
        $destructionId = $this->route('id');
        
        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('destruction', 'name')->ignore($destructionId),
            ],
            'article_ids' => 'sometimes|array|min:1',
            'article_ids.*' => 'integer|exists:articles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Акт уничтожения с таким названием уже существует',
            'article_ids.min' => 'Необходимо указать хотя бы одну статью',
            'article_ids.*.exists' => 'Одна из указанных статей не существует',
        ];
    }
}
