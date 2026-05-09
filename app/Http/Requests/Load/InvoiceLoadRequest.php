<?php

namespace App\Http\Requests\Load;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceLoadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'numero_factura'  => ['required', 'string', 'max:255'],
            'fecha_factura'   => ['required', 'date'],
            'importe_factura' => ['required', 'numeric', 'min:0'],
        ];
    }
}
