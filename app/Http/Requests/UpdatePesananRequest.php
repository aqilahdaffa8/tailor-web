<?php

namespace App\Http\Requests;

class UpdatePesananRequest extends PesananFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tanggal_pesan' => ['required', 'date'],
            'deadline' => ['required', 'date', 'after_or_equal:tanggal_pesan'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
