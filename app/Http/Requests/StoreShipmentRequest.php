<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->companies->contains('id', $this->company_id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id'        => 'required|integer|exists:companies,id',
            'fecha'             => 'required|date',
            'branch_id'         => 'nullable|integer|exists:branches,id',
            'origen_id' => 'nullable|integer|exists:ubicaciones,id',
            'destino_id' => 'nullable|integer|exists:ubicaciones,id',
            'remitente_id' => 'nullable|integer|exists:parties,id',
            'destinatario_id' => 'nullable|integer|exists:parties,id',

            'numero_factura' => 'nullable|string',
            'ubicacion_actual' => 'nullable|in:Dto origen,En transito,Dto destino,En reparto,Entregado',
            'flete_a_pagar_en' => 'nullable|in:origen,destino',
            'fecha_entrega' => 'nullable|date',
            'cobrada' => 'boolean',
            'contra_reembolso' => 'boolean',
            'flete' => 'numeric|min:0',
            'seguro' => 'numeric|min:0',
            'monto_contra_reembolso' => 'numeric|min:0',
            'retencion_mercaderia' => 'numeric|min:0',
            'otros_cargos' => 'numeric|min:0',
            'subtotal' => 'numeric|min:0',
            'iva_monto' => 'numeric|min:0',
            'iva_percent' => 'nullable|numeric|min:0',
            'total' => 'numeric|min:0',
            'notas' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.tipo_paquete' => 'required|in:bultos,palets,sobres',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.numero_remito' => 'nullable|string',
            'items.*.peso' => 'numeric|min:0',
            'items.*.volumen' => 'numeric|min:0',
            'items.*.monto_valor_declarado' => 'numeric|min:0',
            'items.*.monto_seguro_item' => 'numeric|min:0',
            'items.*.referencia_recepcion' => 'nullable|string',
            'items.*.referencia_orden_carga' => 'nullable|string',
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
        ];
    }
}