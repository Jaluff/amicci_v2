<?php

namespace App\Http\Requests\Load;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLoadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id'      => ['required', 'exists:companies,id'],
            'remitente_id'    => ['required', 'exists:parties,id'],
            'destinatario_id' => ['required', 'exists:parties,id'],
            'origen_id'       => ['required', 'exists:branches,id'],
            'destino_id'      => ['required', 'exists:branches,id'],
            'driver_id'       => ['nullable', 'exists:drivers,id'],
            'fecha_carga'     => ['required', 'date'],
            'remito'          => ['nullable', 'string', 'max:255'],
            'observaciones'   => ['nullable', 'string', 'max:1000'],
            'fecha_descarga'  => ['nullable', 'date', 'after_or_equal:fecha_carga'],
            'importe_factura' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
