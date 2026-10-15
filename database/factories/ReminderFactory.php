<?php

namespace Database\Factories;

use App\Models\Reminder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reminder>
 */
class ReminderFactory extends Factory
{
    protected $model = Reminder::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'judul' => fake()->sentence(3),
            'pesan' => fake()->sentence(8),
            'tipe' => 'deadline',
            'referensi_id' => null,
            'referensi_tipe' => null,
            'is_read' => false,
        ];
    }

    public function dibaca(): static
    {
        return $this->state(fn () => ['is_read' => true]);
    }
}
