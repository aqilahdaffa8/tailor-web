<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBahanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'kategori_bahan_id' => ['required', 'integer', 'exists:kategori_bahan,id'],
            'nama_bahan' => ['required', 'string', 'max:100'],
            'satuan' => ['required', 'string', 'max:30'],
            'stok_minimum' => ['required', 'numeric', 'min:0'],
        ];
    }
}
