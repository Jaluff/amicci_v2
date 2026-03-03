<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location_id' => ['required', 'exists:ubicaciones,id'],
            'deliverer_id' => ['required', 'exists:deliverers,id'],
            'load_date' => ['nullable', 'date'],
            'status' => ['required', 'in:Listo,En reparto,Finalizado'],
            'shipments' => ['nullable', 'array'],
            'shipments.*' => ['exists:shipments,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'location_id.required' => 'La ubicación del reparto es obligatoria.',
            'deliverer_id.required' => 'El repartidor es obligatorio.',
            'status.required' => 'El estado es obligatorio.',
            'shipments.array' => 'El formato de las guías es inválido.',
        ];
    }
}