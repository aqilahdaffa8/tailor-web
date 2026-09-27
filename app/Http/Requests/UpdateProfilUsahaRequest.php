<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfilUsahaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_usaha' => ['required', 'string', 'max:100'],
            'pemilik' => ['required', 'string', 'max:100'],
            'no_hp' => ['required', 'string', 'max:30'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'prefix_invoice' => ['nullable', 'string', 'max:15'],
            'catatan_kaki_invoice' => ['nullable', 'string', 'max:500'],
        ];
    }
}
