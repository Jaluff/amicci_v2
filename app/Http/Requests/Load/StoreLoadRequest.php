<?php

namespace App\Http\Requests\Load;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoadRequest extends FormRequest
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
            'observaciones'   => ['nullable', 'string'],
            'importe_factura' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
