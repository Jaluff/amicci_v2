<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDispatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'origin_id' => ['required', 'exists:branches,id'],
            'destination_id' => ['required', 'exists:branches,id'],
            'driver_id' => ['required', 'exists:drivers,id'],
            'status' => ['required', 'in:Cargado,En viaje,Arribado'],
            'seal_number' => ['nullable', 'string', 'max:255'],
            'semi_number' => ['nullable', 'string', 'max:255'],
            'chassis_number' => ['nullable', 'string', 'max:255'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'routes' => ['nullable', 'array'],
            'routes.*' => ['exists:transport_routes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'origin_id.required' => 'El origen es obligatorio.',
            'destination_id.required' => 'El destino es obligatorio.',
            'destination_id.exists' => 'El destino seleccionado no es válido.',
            'driver_id.required' => 'El conductor es obligatorio.',
            'status.in' => 'El estado no es válido.',
        ];
    }
}
