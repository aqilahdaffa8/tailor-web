<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJenisPakaianRequest extends FormRequest
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
            'kategori_pola_id' => ['required', 'integer', 'exists:kategori_pola,id'],
            'nama_jenis' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:500'],
        ];
    }
}
