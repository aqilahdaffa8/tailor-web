<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StokMasukRequest extends FormRequest
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
            'jumlah' => ['required', 'numeric', 'gt:0'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ];
    }
}
