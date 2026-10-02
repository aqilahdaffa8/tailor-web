<?php

namespace Database\Factories;

use App\Models\Pelanggan;
use App\Models\Pesanan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pesanan>
 */
class PesananFactory extends Factory
{
    protected $model = Pesanan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nomor_pesanan' => 'FAC-'.fake()->unique()->numerify('##########'),
            'pelanggan_id' => Pelanggan::factory(),
            'tanggal_pesan' => now()->toDateString(),
            'deadline' => now()->addDays(14)->toDateString(),
            'catatan' => fake()->optional()->sentence(),
            'status' => 'aktif',
        ];
    }

    /**
     * Pesanan aktif yang deadline-nya sudah lewat.
     */
    public function terlambat(): static
    {
        return $this->state(fn () => [
            'tanggal_pesan' => now()->subDays(10)->toDateString(),
            'deadline' => now()->subDays(2)->toDateString(),
            'status' => 'aktif',
        ]);
    }

    /**
     * Pesanan aktif dengan deadline dalam 3 hari ke depan (H-3).
     */
    public function mendekatiDeadline(): static
    {
        return $this->state(fn () => [
            'deadline' => now()->addDays(2)->toDateString(),
            'status' => 'aktif',
        ]);
    }

    public function selesai(): static
    {
        return $this->state(fn () => ['status' => 'selesai']);
    }

    public function dibatalkan(): static
    {
        return $this->state(fn () => ['status' => 'dibatalkan']);
    }
}
