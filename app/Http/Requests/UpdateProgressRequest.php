<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateProgressRequest extends PesananFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'aksi' => ['required', 'string', Rule::in(['maju', 'mundur'])],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'aksi.in' => 'Aksi progress harus berupa "maju" atau "mundur".',
        ]);
    }
}
