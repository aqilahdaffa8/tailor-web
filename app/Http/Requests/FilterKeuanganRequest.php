<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class FilterKeuanganRequest extends KeuanganFormRequest
{
    /**
     * @var array<int, string>
     */
    public const TAB = ['ringkasan', 'pendapatan', 'pengeluaran'];

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'kategori_id' => ['nullable', 'integer', 'exists:kategori_pengeluaran,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'tab' => ['nullable', 'string', Rule::in(self::TAB)],
        ];
    }
}
