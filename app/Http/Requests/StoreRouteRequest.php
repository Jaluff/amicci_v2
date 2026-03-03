<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreRouteRequest extends FormRequest
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
            'origin_id' => ['required', 'exists:ubicaciones,id'],
            'destination_id' => ['required', 'exists:ubicaciones,id', 'different:origin_id'],
            'status' => ['required', 'in:Cargada,Entregada,En viaje,Con problemas'],

            'shipments' => ['nullable', 'array'],
            'shipments.*' => ['exists:shipments,id'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'origin_id.required' => 'El campo origen es obligatorio.',
            'origin_id.exists' => 'El origen seleccionado no es válido.',
            'destination_id.required' => 'El campo destino es obligatorio.',
            'destination_id.exists' => 'El destino seleccionado no es válido.',
            'destination_id.different' => 'El destino debe ser diferente al origen.',
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado seleccionado no es válido.',
            'shipments.array' => 'El formato de las guías es inválido.',
            'shipments.*.exists' => 'Una o más guías seleccionadas no existen.',
        ];
    }
}