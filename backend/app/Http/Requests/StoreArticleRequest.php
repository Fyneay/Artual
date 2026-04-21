<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
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
            'name' => 'required|string',
            //'path-file' => 'required|file|mimes:jpeg,png,pdf|mimetypes:application/pdf,image/jpeg,image/png|max:10240', //file
            'user_id'=> 'required|integer|exists:users,id',
            'secrecy_grade' => 'nullable|boolean',
            'section_id' => 'required|integer|exists:sections,id',
            'list_period_id' => 'nullable|integer|exists:lists_periods,id',
            'location' => 'nullable|string',
            'type_document_id' => 'nullable|integer|exists:types_document,id',
            'description' => 'nullable|string',
            'status_id' => 'nullable|integer|exists:status,id',
            'created_at' => 'nullable|date',
            'updated_at' => 'nullable|date',
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
            'name.required' => 'Название документа должно быть заполнено',
            'user_id.required'=>'Пользователь должен быть заполнен',
            'user_id.exists' => 'Пользователя не существует в базе данных',
            'section_id.required'=>'Раздел для файла должен быть заполнен',
            'section_id.exists'=>'Раздела для сохранения документа не существует',
            'type_document_id.exists' => 'Тип документа не существует в базе данных',
            'status_id.exists' => 'Статус не существует в базе данных',
            'created_at.date' => 'Время создания должен быть в формате даты',
            'updated_at.date' => 'Время изменения должен быть в формате даты',
            // 'path-file.required' => 'Файл должен быть прикреплен',
            // 'path-file.file' => 'Форма для прикрепления файла не должна быть заполнена иной информацией',
            // 'path-file.mimes' => 'Файл должен быть формата изображения или PDF',
            // 'path-file.max' => 'Файл не должен превышать более 10 Мб'
        ];
    }
}
