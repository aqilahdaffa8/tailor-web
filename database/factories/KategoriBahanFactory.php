<?php

namespace Database\Factories;

use App\Models\KategoriBahan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KategoriBahan>
 */
class KategoriBahanFactory extends Factory
{
    protected $model = KategoriBahan::class;

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
