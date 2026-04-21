<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSignatureRequest extends FormRequest
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
            'signature_data' => 'required|string',
            'certificate_name' => 'required|string',
            'certificate_subject' => 'required|string',
            'article_id' => 'sometimes|integer|exists:articles,id'
        ];
    }

    public function messages(): array
    {
        return [
            'signature_data.required' => 'Подпись должна быть заполнена',
            'certificate_name.required' => 'Название сертификата должно быть заполнено',
            'certificate_subject.required' => 'Тема сертификата должна быть заполнена',
            'article_id.exists' => 'Документ не существует',
        ];
    }
}
