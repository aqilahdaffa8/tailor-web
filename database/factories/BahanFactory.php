<?php

namespace Database\Factories;

use App\Models\Bahan;
use App\Models\KategoriBahan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bahan>
 */
class BahanFactory extends Factory
{
    protected $model = Bahan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kategori_bahan_id' => KategoriBahan::factory(),
            'nama_bahan' => fake()->words(2, true),
            'satuan' => fake()->randomElement(['Meter', 'Yard', 'Roll', 'Pcs', 'Bungkus']),
            'stok' => fake()->randomFloat(2, 5, 100),
            'stok_minimum' => fake()->randomFloat(2, 2, 10),
        ];
    }
}
