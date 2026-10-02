<?php

namespace Database\Factories;

use App\Models\ItemPesanan;
use App\Models\ProgressItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgressItem>
 */
class ProgressItemFactory extends Factory
{
    protected $model = ProgressItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_pesanan_id' => ItemPesanan::factory(),
            'nama_tahapan' => fake()->words(2, true),
            'urutan' => fake()->numberBetween(1, 7),
            'is_completed' => false,
            'completed_at' => null,
        ];
    }

    public function selesai(): static
    {
        return $this->state(fn () => [
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }
}
