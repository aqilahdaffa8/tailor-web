<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePelangganRequest extends FormRequest
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
            'nama' => ['required', 'string', 'max:100'],
            'no_hp' => ['required', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }
}
