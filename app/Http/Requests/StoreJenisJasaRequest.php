<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreJenisJasaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_jasa' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:500'],
            'harga_default' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'tahapan' => ['nullable', 'array'],
            'tahapan.*' => ['required', 'string', 'max:100'],
        ];
    }
}
