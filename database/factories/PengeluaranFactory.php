<?php

namespace Database\Factories;

use App\Models\Pengeluaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pengeluaran>
 */
class PengeluaranFactory extends Factory
{
    protected $model = Pengeluaran::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kategori_pengeluaran_id' => null,
            'nominal' => fake()->randomElement([25000, 50000, 120000, 350000]),
            'tanggal' => now()->toDateString(),
            'catatan' => fake()->optional()->sentence(3),
        ];
    }
}
