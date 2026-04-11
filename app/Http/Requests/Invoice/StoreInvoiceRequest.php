<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La autorización de rol se maneja en el middleware de ruta
    }

    public function rules(): array
    {
        return [
            'party_id'      => ['required', 'integer', 'exists:parties,id'],
            'shipment_ids'  => ['required', 'array', 'min:1'],
            'shipment_ids.*'=> ['integer', 'exists:shipments,id'],
            'numero'        => ['required', 'string', 'max:100'],
            'fecha_factura' => ['required', 'date'],
            'numero_recibo' => ['nullable', 'string', 'max:100'],
            'notas'         => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'party_id.required'     => 'Debe seleccionar un cliente para la factura.',
            'shipment_ids.required' => 'Debe seleccionar al menos una guía.',
            'shipment_ids.min'      => 'Debe seleccionar al menos una guía.',
            'numero.required'       => 'El número de factura es obligatorio.',
            'fecha_factura.required'=> 'La fecha de factura es obligatoria.',
            'fecha_factura.date'    => 'La fecha de factura no tiene un formato válido.',
        ];
    }
}
