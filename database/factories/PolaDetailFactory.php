<?php

namespace Database\Factories;

use App\Models\JenisPakaian;
use App\Models\PolaDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PolaDetail>
 */
class PolaDetailFactory extends Factory
{
    protected $model = PolaDetail::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'jenis_pakaian_id' => JenisPakaian::factory(),
            'size' => fake()->randomElement(['S', 'M', 'L', 'XL', 'XXL', 'Custom']),
            'data_ukuran' => [
                'lingkar_dada' => fake()->numberBetween(80, 120),
                'panjang_baju' => fake()->numberBetween(60, 85),
                'panjang_lengan' => fake()->numberBetween(50, 65),
                'lebar_bahu' => fake()->numberBetween(38, 50),
            ],
            'foto_pola' => null,
            'file_pola' => null,
            'catatan' => fake()->optional()->sentence(),
        ];
    }
}
