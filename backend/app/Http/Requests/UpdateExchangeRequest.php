<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExchangeRequest extends FormRequest
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
        $exchangeId = $this->route('id');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('exchange', 'name')->ignore($exchangeId),
            ],
            'reason' => 'sometimes|nullable|string',
            'fund_name' => 'sometimes|nullable|string|max:255',
            'receiving_organization' => 'sometimes|nullable|string|max:255',
            'article_ids' => 'sometimes|array|min:1',
            'article_ids.*' => 'integer|exists:articles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Акт приема с таким названием уже существует',
            'article_ids.min' => 'Необходимо указать хотя бы одну статью',
            'article_ids.*.exists' => 'Одна из указанных статей не существует',
        ];
    }
}
