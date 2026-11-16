<?php

namespace Database\Factories;

use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        $title = rtrim($this->faker->sentence(6), '.');

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1, 99999),
            'tag' => $this->faker->randomElement(['Recorrido', 'Propuesta', 'Evento', 'Comunicado']),
            'summary' => $this->faker->sentence(14),
            'body' => $this->faker->paragraphs(4, true),
            'image_path' => null,
            'image_caption' => $this->faker->sentence(4),
            'published' => true,
            'published_at' => now()->subDays($this->faker->numberBetween(0, 30)),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['published' => false, 'published_at' => null]);
    }
}
