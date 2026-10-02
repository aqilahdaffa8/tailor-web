<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StorePesananRequest extends PesananFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'pelanggan_id' => ['required', 'integer', 'exists:pelanggan,id'],
            'tanggal_pesan' => ['required', 'date'],
            'deadline' => ['required', 'date', 'after_or_equal:tanggal_pesan'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.jenis_jasa_id' => ['required', 'integer', 'exists:jenis_jasa,id'],
            'items.*.pola_detail_id' => ['nullable', 'integer', 'exists:pola_detail,id'],
            'items.*.pengukuran_id' => [
                'nullable',
                'integer',
                Rule::exists('pengukuran', 'id')->where('pelanggan_id', $this->input('pelanggan_id')),
            ],
            'items.*.harga' => ['required', 'numeric', 'min:0'],
            'items.*.deadline' => ['nullable', 'date', 'after_or_equal:tanggal_pesan'],
            'items.*.detail_pekerjaan' => ['nullable', 'string', 'max:1000'],
            'items.*.catatan' => ['nullable', 'string', 'max:1000'],
            'dp' => ['nullable', 'numeric', 'min:0'],
            'metode_dp' => ['nullable', Rule::in(self::METODE_PEMBAYARAN)],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'items.*.pengukuran_id.exists' => 'Pengukuran yang dipilih bukan milik pelanggan ini.',
            'items.required' => 'Minimal satu item pesanan harus ditambahkan.',
            'items.min' => 'Minimal satu item pesanan harus ditambahkan.',
        ]);
    }
}
