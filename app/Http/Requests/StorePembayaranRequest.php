<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StorePembayaranRequest extends PesananFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'jumlah' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'metode' => ['required', 'string', Rule::in(self::METODE_PEMBAYARAN)],
            'tanggal_bayar' => ['nullable', 'date', 'before_or_equal:today'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }
}
