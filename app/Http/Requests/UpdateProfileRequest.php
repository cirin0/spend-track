<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->user()->id)],
            'avatar' => ['sometimes', 'image', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Ім\'я має бути текстом',
            'name.max' => 'Ім\'я не може бути довшим за 255 символів',
            'email.email' => 'Невірний формат email',
            'email.unique' => 'Цей email вже використовується',
            'avatar.image' => 'Файл має бути зображенням',
            'avatar.mimes' => 'Дозволені формати: jpeg, jpg, png, gif',
            'avatar.max' => 'Розмір файлу не може перевищувати 2MB',
        ];
    }
}
