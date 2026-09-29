<?php

namespace Database\Factories;

use App\Models\Peralatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Peralatan>
 */
class PeralatanFactory extends Factory
{
    protected $model = Peralatan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_alat' => fake()->words(2, true),
            'jumlah' => fake()->numberBetween(1, 10),
            'kondisi' => fake()->randomElement(['Baik', 'Rusak Ringan', 'Rusak Berat', 'Perlu Servis']),
            'tanggal_beli' => fake()->date(),
            'catatan' => fake()->optional()->sentence(),
        ];
    }
}
