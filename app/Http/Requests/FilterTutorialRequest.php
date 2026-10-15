<?php

namespace App\Http\Requests;

use App\Models\Tutorial;
use Illuminate\Validation\Rule;

class FilterTutorialRequest extends TutorialFormRequest
{
    protected function filePdfWajib(): bool
    {
        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'kategori' => ['nullable', 'string', 'max:50'],
            'tipe' => ['nullable', Rule::in(Tutorial::TIPE)],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
