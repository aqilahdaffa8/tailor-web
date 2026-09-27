<?php

namespace Database\Factories;

use App\Models\JenisPakaian;
use App\Models\KategoriPola;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JenisPakaian>
 */
class JenisPakaianFactory extends Factory
{
    protected $model = JenisPakaian::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kategori_pola_id' => KategoriPola::factory(),
            'nama_jenis' => fake()->words(2, true),
            'deskripsi' => fake()->optional()->sentence(),
        ];
    }
}
