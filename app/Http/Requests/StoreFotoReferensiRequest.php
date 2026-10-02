<?php

namespace App\Http\Requests;

class StoreFotoReferensiRequest extends PesananFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ];
    }
}
