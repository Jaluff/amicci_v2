<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo el admin puede modificar facturas existentes (guard adicional en middleware de ruta)
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'shipment_ids'   => ['sometimes', 'array', 'min:1'],
            'shipment_ids.*' => ['integer', 'exists:shipments,id'],
            'numero'         => ['sometimes', 'string', 'max:100'],
            'fecha_factura'  => ['sometimes', 'date'],
            'numero_recibo'  => ['nullable', 'string', 'max:100'],
            'notas'          => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'shipment_ids.min'  => 'La factura debe contener al menos una guía.',
            'fecha_factura.date'=> 'La fecha de factura no tiene un formato válido.',
        ];
    }
}
