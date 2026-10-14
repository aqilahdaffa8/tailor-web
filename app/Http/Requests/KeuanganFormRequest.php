<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Basis FormRequest modul Keuangan: otorisasi, pesan, dan nama atribut berbahasa Indonesia.
 */
abstract class KeuanganFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'numeric' => ':attribute harus berupa angka.',
            'decimal' => ':attribute maksimal memiliki :decimal angka di belakang koma.',
            'date' => ':attribute bukan tanggal yang valid.',
            'exists' => ':attribute yang dipilih tidak valid.',
            'in' => ':attribute yang dipilih tidak valid.',
            'after_or_equal' => ':attribute tidak boleh lebih awal dari :date.',
            'before_or_equal' => ':attribute tidak boleh lebih dari :date.',
            'min' => [
                'numeric' => ':attribute minimal :min.',
            ],
            'max' => [
                'numeric' => ':attribute maksimal :max.',
                'string' => ':attribute maksimal :max karakter.',
            ],
            'gt' => [
                'numeric' => ':attribute harus lebih besar dari :value.',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'dari' => 'Tanggal awal',
            'sampai' => 'Tanggal akhir',
            'kategori_id' => 'Kategori',
            'kategori_pengeluaran_id' => 'Kategori pengeluaran',
            'nominal' => 'Nominal',
            'tanggal' => 'Tanggal',
            'catatan' => 'Catatan',
            'per_page' => 'Jumlah per halaman',
            'tab' => 'Tab',
        ];
    }
}
