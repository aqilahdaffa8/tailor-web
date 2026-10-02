<?php

namespace App\Http\Requests;

class StorePemakaianBahanRequest extends PesananFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'bahan_id' => ['required', 'integer', 'exists:bahan,id'],
            'jumlah' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }
}
