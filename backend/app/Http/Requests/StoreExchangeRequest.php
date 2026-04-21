<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExchangeRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:exchange,name',
            'reason' => 'nullable|string',
            'fund_name' => 'nullable|string|max:255',
            'receiving_organization' => 'nullable|string|max:255',
            'article_ids' => 'required|array|min:1',
            'article_ids.*' => 'integer|exists:articles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Название акта приема обязательно',
            'name.unique' => 'Акт приема с таким названием уже существует',
            'article_ids.required' => 'Необходимо указать хотя бы одну статью',
            'article_ids.min' => 'Необходимо указать хотя бы одну статью',
            'article_ids.*.exists' => 'Одна из указанных статей не существует',
        ];
    }
}
