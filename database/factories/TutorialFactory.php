<?php

namespace Database\Factories;

use App\Models\Tutorial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tutorial>
 */
class TutorialFactory extends Factory
{
    protected $model = Tutorial::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'judul' => fake()->sentence(4),
            'kategori' => fake()->randomElement(['Pengukuran', 'Menjahit', 'Perawatan Mesin']),
            'tipe' => 'artikel',
            'konten' => fake()->paragraphs(2, true),
            'file_or_url' => null,
        ];
    }

    public function video(string $url = 'https://www.youtube.com/watch?v=abcdefghijk'): static
    {
        return $this->state(fn () => ['tipe' => 'video', 'file_or_url' => $url]);
    }

    public function pdf(string $path = 'tutorial/pdf/contoh.pdf'): static
    {
        return $this->state(fn () => ['tipe' => 'pdf', 'file_or_url' => $path]);
    }
}
