<?php

namespace Database\Factories;

use App\Models\Visit;
use App\Support\Analytics\SourceClassifier;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitFactory extends Factory
{
    protected $model = Visit::class;

    public function definition(): array
    {
        return [
            'session_hash' => hash('sha256', $this->faker->uuid()),
            'is_entry' => true,
            'path' => $this->faker->randomElement(['/', '/quien-es-laura', '/propuestas', '/voluntarios']),
            'route' => 'home',
            'status' => 200,
            'channel' => SourceClassifier::DIRECT,
            'ip' => $this->faker->ipv4(),
            'device' => 'movil',
            'browser' => 'Chrome',
            'os' => 'Android',
            'created_at' => now(),
        ];
    }

    public function fromAi(string $name = 'ChatGPT'): static
    {
        return $this->state(['channel' => SourceClassifier::AI, 'source_name' => $name, 'referrer_host' => 'chatgpt.com']);
    }

    public function bot(string $name = 'GPTBot (OpenAI)', bool $ai = true): static
    {
        return $this->state(['channel' => SourceClassifier::BOT, 'is_bot' => true, 'is_ai_bot' => $ai, 'bot_name' => $name, 'device' => 'bot', 'is_entry' => false]);
    }
}
