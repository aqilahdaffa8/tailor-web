<?php

namespace Database\Seeders;

use App\Models\JenisJasa;
use App\Models\KategoriBahan;
use App\Models\KategoriPengeluaran;
use App\Models\KategoriPola;
use App\Models\KategoriTutorial;
use App\Models\Pengaturan;
use App\Models\TemplateTahapan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TailorSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. User default (Owner)
        User::firstOrCreate(
            ['email' => 'owner@tailor.test'],
            [
                'name' => 'Pemilik Tailor',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Jenis Jasa & Template Tahapan
        $daftarJasa = [
            [
                'nama' => 'Jahit Kemeja',
                'deskripsi' => 'Pembuatan kemeja pria/wanita dari bahan mentah',
                'harga' => 150000,
                'tahapan' => [
                    'Menunggu Dikerjakan',
                    'Pengukuran',
                    'Pemotongan',
                    'Penjahitan',
                    'Finishing',
                    'Selesai',
                    'Diambil Pelanggan',
                ],
            ],
            [
                'nama' => 'Jahit Celana',
                'deskripsi' => 'Pembuatan celana panjang/pendek formal/santai',
                'harga' => 120000,
                'tahapan' => [
                    'Menunggu Dikerjakan',
                    'Pengukuran',
                    'Pemotongan',
                    'Penjahitan',
                    'Finishing',
                    'Selesai',
                    'Diambil Pelanggan',
                ],
            ],
            [
                'nama' => 'Permak Pakaian',
                'deskripsi' => 'Mengecilkan, membesarkan, atau mengubah model pakaian jadi',
                'harga' => 35000,
                'tahapan' => [
                    'Menunggu Dikerjakan',
                    'Pengerjaan',
                    'Finishing',
                    'Selesai',
                    'Diambil Pelanggan',
                ],
            ],
            [
                'nama' => 'Ganti Resleting',
                'deskripsi' => 'Penggantian resleting celana, jaket, atau rok',
                'harga' => 20000,
                'tahapan' => [
                    'Menunggu Dikerjakan',
                    'Pengerjaan',
                    'Finishing',
                    'Selesai',
                    'Diambil Pelanggan',
                ],
            ],
            [
                'nama' => 'Potong Bahan / Celana',
                'deskripsi' => 'Pemotongan panjang celana atau kain',
                'harga' => 15000,
                'tahapan' => [
                    'Menunggu Dikerjakan',
                    'Pemotongan',
                    'Selesai',
                    'Diambil Pelanggan',
                ],
            ],
        ];

        foreach ($daftarJasa as $item) {
            $jasa = JenisJasa::firstOrCreate(
                ['nama_jasa' => $item['nama']],
                [
                    'deskripsi' => $item['deskripsi'],
                    'harga_default' => $item['harga'],
                    'is_active' => true,
                ]
            );

            // Seed template tahapan jika belum ada
            if ($jasa->templateTahapan()->count() === 0) {
                foreach ($item['tahapan'] as $index => $namaTahapan) {
                    TemplateTahapan::create([
                        'jenis_jasa_id' => $jasa->id,
                        'nama_tahapan' => $namaTahapan,
                        'urutan' => $index + 1,
                    ]);
                }
            }
        }

        // 3. Kategori Pola
        $kategoriPola = ['Kemeja', 'Celana', 'Dress', 'Gamis', 'Jas', 'Blouse', 'Rok'];
        foreach ($kategoriPola as $kat) {
            KategoriPola::firstOrCreate(['nama_kategori' => $kat]);
        }

        // 4. Kategori Bahan
        $kategoriBahan = ['Kain', 'Benang', 'Resleting', 'Kancing', 'Interfacing / Furing', 'Aksesoris'];
        foreach ($kategoriBahan as $kat) {
            KategoriBahan::firstOrCreate(['nama_kategori' => $kat]);
        }

        // 5. Kategori Pengeluaran
        $kategoriPengeluaran = ['Pembelian Bahan', 'Pembelian Alat', 'Perawatan Mesin', 'Operasional & Listrik', 'Lain-lain'];
        foreach ($kategoriPengeluaran as $kat) {
            KategoriPengeluaran::firstOrCreate(['nama_kategori' => $kat]);
        }

        // 6. Kategori Tutorial
        $kategoriTutorial = ['Teknik Menjahit', 'Pembuatan Pola', 'Pengukuran', 'Perawatan Mesin', 'Finishing & Permak', 'Lain-lain'];
        foreach ($kategoriTutorial as $kat) {
            KategoriTutorial::firstOrCreate(['nama_kategori' => $kat]);
        }

        // 7. Pengaturan Profil Usaha & Invoice
        Pengaturan::firstOrCreate(
            ['key' => 'profil_usaha'],
            [
                'value' => json_encode([
                    'nama_usaha' => 'Tailor Mandiri',
                    'pemilik' => 'Pemilik Tailor',
                    'no_hp' => '08123456789',
                    'alamat' => 'Jl. Penjahit Kreatif No. 12',
                ]),
            ]
        );

        Pengaturan::firstOrCreate(
            ['key' => 'pengaturan_invoice'],
            [
                'value' => json_encode([
                    'prefix' => 'ORD',
                    'catatan_kaki' => 'Barang yang sudah diambil lebih dari 30 hari di luar tanggung jawab kami.',
                ]),
            ]
        );
    }
}
