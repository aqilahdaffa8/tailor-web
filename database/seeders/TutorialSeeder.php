<?php

namespace Database\Seeders;

use App\Models\KategoriTutorial;
use App\Models\Tutorial;
use Illuminate\Database\Seeder;

class TutorialSeeder extends Seeder
{
    /**
     * Contoh tutorial awal. Aman dijalankan berulang (berdasarkan judul).
     */
    public function run(): void
    {
        $contoh = [
            [
                'judul' => 'Cara Mengambil Ukuran Badan',
                'kategori' => 'Pengukuran',
                'konten' => "Pastikan pelanggan berdiri tegak dan rileks.\nGunakan pita ukur dan jangan menarik terlalu kencang.\nCatat ukuran lingkar dada, pinggang, pinggul, panjang lengan, dan panjang baju.\nTambahkan kelonggaran sesuai jenis pakaian saat membuat pola.",
            ],
            [
                'judul' => 'Perawatan Dasar Mesin Jahit',
                'kategori' => 'Perawatan Mesin',
                'konten' => "Bersihkan debu dan sisa benang pada area gigi dan sekoci setiap selesai pekerjaan besar.\nTeteskan sedikit minyak mesin jahit pada bagian yang bergerak.\nGanti jarum secara berkala, terutama bila mulai tumpul atau melewatkan tusukan.",
            ],
        ];

        foreach ($contoh as $data) {
            $kat = KategoriTutorial::firstOrCreate(['nama_kategori' => $data['kategori']]);
            Tutorial::firstOrCreate(
                ['judul' => $data['judul']],
                $data + ['kategori_tutorial_id' => $kat->id, 'tipe' => 'artikel', 'file_or_url' => null],
            );
        }
    }
}
