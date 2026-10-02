<?php

namespace Database\Factories;

use App\Models\ItemPesanan;
use App\Models\JenisJasa;
use App\Models\Pesanan;
use App\Models\TemplateTahapan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Catatan: jenis_jasa_id diambil dari data seeder (JenisJasa beserta template tahapannya).
 *
 * @extends Factory<ItemPesanan>
 */
class ItemPesananFactory extends Factory
{
    protected $model = ItemPesanan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pesanan_id' => Pesanan::factory(),
            'jenis_jasa_id' => fn () => JenisJasa::query()->orderBy('id')->value('id'),
            'pola_detail_id' => null,
            'pengukuran_id' => null,
            'detail_pekerjaan' => fake()->sentence(3),
            'harga' => fake()->randomElement([35000, 75000, 120000, 150000, 250000]),
            'deadline' => now()->addDays(14)->toDateString(),
            'catatan' => fake()->optional()->sentence(),
            'status_item' => 'menunggu',
        ];
    }

    /**
     * Salin template tahapan jenis jasa ke progress_item setelah item dibuat.
     */
    public function denganProgress(): static
    {
        return $this->afterCreating(function (ItemPesanan $item): void {
            TemplateTahapan::query()
                ->where('jenis_jasa_id', $item->jenis_jasa_id)
                ->orderBy('urutan')
                ->get()
                ->each(fn (TemplateTahapan $tahap) => $item->progress()->create([
                    'nama_tahapan' => $tahap->nama_tahapan,
                    'urutan' => $tahap->urutan,
                    'is_completed' => false,
                ]));
        });
    }
}
