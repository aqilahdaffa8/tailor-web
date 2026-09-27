<?php

namespace Database\Factories;

use App\Models\Pelanggan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pelanggan>
 */
class PelangganFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'no_hp' => '08'.fake()->numerify('#########'),
            'alamat' => fake()->address(),
            'catatan' => fake()->optional(0.3)->sentence(),
        ];
    }
}
