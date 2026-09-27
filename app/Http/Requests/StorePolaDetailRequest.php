<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePolaDetailRequest extends FormRequest
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
            'jenis_pakaian_id' => ['required', 'integer', 'exists:jenis_pakaian,id'],
            'size' => ['required', 'string', 'max:20'],
            'data_ukuran' => ['nullable', 'array'],
            'foto_pola' => ['nullable', 'image', 'max:5120'],
            'file_pola' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
