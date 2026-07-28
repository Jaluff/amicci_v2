<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class EntityShipmentFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'ubicacion' => 'nullable|string',
            'entidad' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
