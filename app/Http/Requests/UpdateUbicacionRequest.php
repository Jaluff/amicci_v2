<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUbicacionRequest extends FormRequest
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
        $id = $this->route('ubicacione'); // Laravel defaults the parameter name for resource routes

        return [
            'nombre' => 'required|string|max:255|unique:ubicaciones,nombre,' . $id,
            'branch_id' => 'required|exists:branches,id',
        ];
    }
}
