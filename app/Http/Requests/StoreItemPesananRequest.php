<?php

namespace App\Http\Requests;

use App\Models\Pesanan;
use Illuminate\Validation\Rule;

class StoreItemPesananRequest extends PesananFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $pesanan = $this->route('pesanan');
        $pelangganId = $pesanan instanceof Pesanan ? $pesanan->pelanggan_id : null;

        return [
            'jenis_jasa_id' => ['required', 'integer', 'exists:jenis_jasa,id'],
            'pola_detail_id' => ['nullable', 'integer', 'exists:pola_detail,id'],
            'pengukuran_id' => [
                'nullable',
                'integer',
                Rule::exists('pengukuran', 'id')->where('pelanggan_id', $pelangganId),
            ],
            'harga' => ['required', 'numeric', 'min:0'],
            'deadline' => ['nullable', 'date'],
            'detail_pekerjaan' => ['nullable', 'string', 'max:1000'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'pengukuran_id.exists' => 'Pengukuran yang dipilih bukan milik pelanggan pesanan ini.',
        ]);
    }
}
