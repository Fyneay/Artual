<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileCheckRequest extends FormRequest
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
            'file' => 'required|file|max:102400', // Максимум 100MB
            'file_id' => 'nullable|integer', // Опциональный ID файла из БД
            'article_id' => 'required|integer|exists:articles,id', // ID статьи
            'metadata' => 'nullable|array', // Дополнительные метаданные
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
            'file.required' => 'Файл должен быть прикреплен',
            'file.file' => 'Поле должно содержать файл',
            'file.max' => 'Файл не должен превышать 100 МБ',
            'file_id.integer' => 'ID файла должен быть числом',
            'metadata.array' => 'Метаданные должны быть массивом',
        ];
    }
}

