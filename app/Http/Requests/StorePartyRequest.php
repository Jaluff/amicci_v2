<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => 'required|string|max:255',
            'document'          => 'nullable|string|max:100',
            'document_type'     => 'nullable|string|max:50',
            'tax_status'        => 'nullable|string|max:100',
            'iva_percent'       => 'nullable|numeric|min:0|max:100',
            'has_insurance'     => 'nullable|in:true,false,1,0',
            'insurance_percent' => 'nullable|numeric|min:0|max:100',
            'phone'             => 'nullable|string|max:50',
            'phone_secondary'   => 'nullable|string|max:50',
            'email'             => 'nullable|email|max:255',

            // Array of addresses (optional now)
            'addresses' => 'nullable|array',
            'addresses.*.id' => 'nullable',
            // Here, if they send an address, type is required, address_line1 is optional wait, no, type might be there but empty line1. Let's make type required if addresses exist.
            'addresses.*.type' => 'required_with:addresses|string|max:50',
            'addresses.*.address_line1' => 'nullable|string|max:255',
            'addresses.*.city' => 'nullable|string|max:100',
            // if we are sending state but it might be optional:
            'addresses.*.state' => 'nullable|string|max:100',
            'addresses.*.zip_code' => 'nullable|string|max:20',
            'addresses.*.phone' => 'nullable|string|max:100',
            'addresses.*.email' => 'nullable|email|max:255',
            'addresses.*.is_primary' => 'nullable',

            // Tariff settings
            'tariff' => 'nullable|array',
            'tariff.billing_mode' => 'nullable|string|max:50',
            'tariff.minimum_charge' => 'nullable|numeric|min:0',
            'tariff.rate_per_ton_custom' => 'nullable|numeric|min:0',
            'tariff.rate_per_m3_custom' => 'nullable|numeric|min:0',
            'tariff.rate_per_bulto' => 'nullable|numeric|min:0',
            'tariff.minimum_per_bulto' => 'nullable|numeric|min:0',
            'tariff.rate_per_pallet' => 'nullable|numeric|min:0',
            'tariff.minimum_per_pallet' => 'nullable|numeric|min:0',
            'tariff.declared_value_pct' => 'nullable|numeric|min:0',
            'tariff.valid_from' => 'nullable|date',
            'tariff.valid_until' => 'nullable|date',
            'tariff.notes' => 'nullable|string',
        ];
    }
}
