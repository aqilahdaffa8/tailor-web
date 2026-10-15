<?php

namespace App\Http\Requests;

use App\Models\Tutorial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Basis FormRequest modul Tutorial: aturan bersama, pesan, dan atribut berbahasa Indonesia.
 */
abstract class TutorialFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * File PDF wajib diunggah bila tipe pdf (pada update: hanya bila sebelumnya bukan pdf).
     */
    abstract protected function filePdfWajib(): bool;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:50'],
            'tipe' => ['required', Rule::in(Tutorial::TIPE)],
            'konten' => [
                Rule::requiredIf(fn () => $this->input('tipe') === 'artikel'),
                'nullable', 'string', 'max:50000',
            ],
            'video_url' => [
                Rule::requiredIf(fn () => $this->input('tipe') === 'video'),
                'nullable', 'string', 'max:500',
                function (string $attribute, mixed $value, \Closure $gagal): void {
                    if ($this->input('tipe') === 'video' && $value && ! Tutorial::idYoutube((string) $value)) {
                        $gagal('Tautan video harus berupa tautan YouTube yang valid.');
                    }
                },
            ],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'hapus_gambar' => ['nullable', 'boolean'],
            'file' => [
                Rule::requiredIf(fn () => $this->input('tipe') === 'pdf' && $this->filePdfWajib()),
                'nullable', 'file', 'mimes:pdf', 'max:10240',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'in' => ':attribute yang dipilih tidak valid.',
            'boolean' => ':attribute tidak valid.',
            'image' => ':attribute harus berupa gambar.',
            'file' => ':attribute harus berupa file.',
            'mimes' => ':attribute harus berformat :values.',
            'max' => [
                'string' => ':attribute maksimal :max karakter.',
                'file' => ':attribute maksimal :max KB.',
                'numeric' => ':attribute maksimal :max.',
            ],
            'integer' => ':attribute harus berupa bilangan bulat.',
            'min' => ['numeric' => ':attribute minimal :min.'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'judul' => 'Judul',
            'kategori' => 'Kategori',
            'tipe' => 'Tipe',
            'konten' => 'Isi/deskripsi',
            'video_url' => 'Tautan video',
            'gambar' => 'Gambar sampul',
            'file' => 'File PDF',
            'search' => 'Kata kunci',
            'per_page' => 'Jumlah per halaman',
        ];
    }
}
