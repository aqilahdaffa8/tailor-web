<?php

namespace Database\Factories;

use App\Models\KategoriPola;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KategoriPola>
 */
class KategoriPolaFactory extends Factory
{
    protected $model = KategoriPola::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_kategori' => fake()->unique()->word(),
            'deskripsi' => fake()->optional()->sentence(),
        ];
    }
}
