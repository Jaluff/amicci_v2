<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AjaxStorePartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'document_type' => 'nullable|string|max:50',
            'document' => 'nullable|string|max:100',
            'tax_status' => 'nullable|string|max:100',
            'iva_percent' => 'nullable|numeric|min:0|max:100',
            'has_insurance' => 'nullable|in:true,false,1,0',
            'insurance_percent' => 'nullable|numeric|min:0|max:100',
        ];
    }
}
