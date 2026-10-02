<?php

namespace Database\Factories;

use App\Models\FotoReferensi;
use App\Models\ItemPesanan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FotoReferensi>
 */
class FotoReferensiFactory extends Factory
{
    protected $model = FotoReferensi::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_pesanan_id' => ItemPesanan::factory(),
            'path_foto' => 'foto-referensi/'.fake()->uuid().'.jpg',
            'keterangan' => fake()->optional()->sentence(3),
        ];
    }
}
