<?php

namespace App\Http\Requests\Load;

use Illuminate\Foundation\Http\FormRequest;

class PayLoadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'numero_recibo' => ['required', 'string', 'max:255'],
            'fecha_recibo'  => ['required', 'date'],
        ];
    }
}
