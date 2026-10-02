<?php

namespace Database\Factories;

use App\Models\Bahan;
use App\Models\ItemPesanan;
use App\Models\PemakaianBahan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Catatan: insert ke tabel pemakaian_bahan memicu trigger MySQL yang memotong stok bahan.
 *
 * @extends Factory<PemakaianBahan>
 */
class PemakaianBahanFactory extends Factory
{
    protected $model = PemakaianBahan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_pesanan_id' => ItemPesanan::factory(),
            'bahan_id' => Bahan::factory(),
            'jumlah' => fake()->randomFloat(2, 0.5, 3),
            'catatan' => fake()->optional()->sentence(),
        ];
    }
}
