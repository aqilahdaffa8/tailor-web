<?php

namespace Database\Factories;

use App\Models\Pelanggan;
use App\Models\Pengukuran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pengukuran>
 */
class PengukuranFactory extends Factory
{
    protected $model = Pengukuran::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pelanggan_id' => Pelanggan::factory(),
            'tanggal_ukur' => fake()->date(),
            'data_ukuran' => [
                'lingkar_leher' => fake()->numberBetween(36, 45),
                'lingkar_dada' => fake()->numberBetween(84, 115),
                'lingkar_pinggang' => fake()->numberBetween(70, 105),
                'lingkar_pinggul' => fake()->numberBetween(88, 115),
                'lebar_bahu' => fake()->numberBetween(40, 50),
                'panjang_lengan' => fake()->numberBetween(54, 66),
                'panjang_baju' => fake()->numberBetween(65, 80),
                'panjang_celana' => fake()->numberBetween(90, 105),
                'lingkar_paha' => fake()->numberBetween(50, 70),
            ],
            'catatan' => fake()->optional()->sentence(),
        ];
    }
}
