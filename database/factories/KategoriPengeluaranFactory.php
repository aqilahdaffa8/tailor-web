<?php

namespace Database\Factories;

use App\Models\KategoriPengeluaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KategoriPengeluaran>
 */
class KategoriPengeluaranFactory extends Factory
{
    protected $model = KategoriPengeluaran::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_kategori' => fake()->unique()->word(),
        ];
    }
}
