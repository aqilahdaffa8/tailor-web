<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengukuranRequest extends FormRequest
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
            'pelanggan_id' => ['required', 'integer', 'exists:pelanggan,id'],
            'tanggal_ukur' => ['required', 'date'],
            'data_ukuran' => ['required', 'array'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
