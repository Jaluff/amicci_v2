<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class EntityLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'nullable|email',
            'correo' => 'nullable|email',
            'password' => 'required|string',
        ];
    }

    public function getLoginEmail(): ?string
    {
        return $this->input('email') ?? $this->input('correo');
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->getLoginEmail()) {
                $validator->errors()->add('email', 'Por favor ingrese correo o email.');
            }
        });
    }
}
