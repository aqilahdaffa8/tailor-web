<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePeralatanRequest extends FormRequest
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
            'nama_alat' => ['required', 'string', 'max:100'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'kondisi' => ['required', 'string', 'in:Baik,Rusak Ringan,Rusak Berat,Perlu Servis'],
            'tanggal_beli' => ['nullable', 'date'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }
}
