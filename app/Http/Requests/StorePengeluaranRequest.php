<?php

namespace App\Http\Requests;

class StorePengeluaranRequest extends KeuanganFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'kategori_pengeluaran_id' => ['nullable', 'integer', 'exists:kategori_pengeluaran,id'],
            'nominal' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:9999999999.99'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }
}
