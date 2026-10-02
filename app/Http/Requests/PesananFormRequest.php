<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Basis FormRequest modul Pesanan: otorisasi, pesan, dan nama atribut berbahasa Indonesia.
 */
abstract class PesananFormRequest extends FormRequest
{
    /**
     * @var array<int, string>
     */
    public const METODE_PEMBAYARAN = ['tunai', 'transfer', 'qris'];

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
            'array' => ':attribute harus berupa daftar.',
            'exists' => ':attribute yang dipilih tidak valid.',
            'in' => ':attribute yang dipilih tidak valid.',
            'image' => ':attribute harus berupa gambar.',
            'mimes' => ':attribute harus berformat: :values.',
            'after_or_equal' => ':attribute tidak boleh lebih awal dari :date.',
            'min' => [
                'numeric' => ':attribute minimal :min.',
                'string' => ':attribute minimal :min karakter.',
                'array' => ':attribute minimal memiliki :min data.',
            ],
            'max' => [
                'numeric' => ':attribute maksimal :max.',
                'string' => ':attribute maksimal :max karakter.',
                'file' => ':attribute maksimal :max KB.',
                'array' => ':attribute maksimal memiliki :max data.',
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
            'pelanggan_id' => 'Pelanggan',
            'tanggal_pesan' => 'Tanggal pesan',
            'deadline' => 'Deadline',
            'catatan' => 'Catatan',
            'items' => 'Item pesanan',
            'items.*.jenis_jasa_id' => 'Jenis jasa',
            'items.*.pola_detail_id' => 'Pola',
            'items.*.pengukuran_id' => 'Pengukuran',
            'items.*.harga' => 'Harga',
            'items.*.deadline' => 'Deadline item',
            'items.*.detail_pekerjaan' => 'Detail pekerjaan',
            'items.*.catatan' => 'Catatan item',
            'jenis_jasa_id' => 'Jenis jasa',
            'pola_detail_id' => 'Pola',
            'pengukuran_id' => 'Pengukuran',
            'harga' => 'Harga',
            'detail_pekerjaan' => 'Detail pekerjaan',
            'dp' => 'Nominal DP',
            'metode_dp' => 'Metode pembayaran DP',
            'aksi' => 'Aksi progress',
            'bahan_id' => 'Bahan',
            'jumlah' => 'Jumlah',
            'foto' => 'Foto referensi',
            'keterangan' => 'Keterangan',
        ];
    }
}
