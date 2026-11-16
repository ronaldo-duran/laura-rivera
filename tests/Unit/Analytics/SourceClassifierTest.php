<?php

namespace Tests\Unit\Analytics;

use App\Support\Analytics\SourceClassifier as S;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SourceClassifierTest extends TestCase
{
    public static function cases(): array
    {
        return [
            'chatgpt' => ['chatgpt.com', null, S::AI, 'ChatGPT'],
            'chatgpt antiguo' => ['chat.openai.com', null, S::AI, 'ChatGPT'],
            'utm de chatgpt' => [null, 'chatgpt.com', S::AI, 'ChatGPT'],
            'claude' => ['claude.ai', null, S::AI, 'Claude'],
            'gemini' => ['gemini.google.com', null, S::AI, 'Gemini'],
            'perplexity' => ['www.perplexity.ai', null, S::AI, 'Perplexity'],
            'copilot' => ['copilot.microsoft.com', null, S::AI, 'Copilot'],
            'meta ai' => ['www.meta.ai', null, S::AI, 'Meta AI'],
            'google colombia' => ['www.google.com.co', null, S::SEARCH, 'Google'],
            'google' => ['google.com', null, S::SEARCH, 'Google'],
            'bing' => ['www.bing.com', null, S::SEARCH, 'Bing'],
            'facebook móvil' => ['m.facebook.com', null, S::SOCIAL, 'Facebook'],
            'facebook enlace' => ['l.facebook.com', null, S::SOCIAL, 'Facebook'],
            'instagram' => ['l.instagram.com', null, S::SOCIAL, 'Instagram'],
            'x' => ['t.co', null, S::SOCIAL, 'X (Twitter)'],
            'campaña' => ['m.facebook.com', 'facebook', S::CAMPAIGN, 'facebook / anuncio'],
            'otro sitio' => ['periodico.com.co', null, S::REFERRAL, 'periodico.com.co'],
            'directo' => [null, null, S::DIRECT, null],
            'interno' => ['laurarivera.co', null, S::INTERNAL, null],
            'no confunde dominios parecidos' => ['notclaude.ai', null, S::REFERRAL, 'notclaude.ai'],
        ];
    }

    #[DataProvider('cases')]
    public function test_classification(?string $host, ?string $utm, string $channel, ?string $name): void
    {
        $medium = $channel === S::CAMPAIGN ? 'anuncio' : null;
        $c = S::classify($host, $utm, $medium, 'www.laurarivera.co');

        $this->assertSame($channel, $c['channel']);
        $this->assertSame($name, $c['name']);
    }

    public function test_label(): void
    {
        $this->assertSame('Asistentes de IA · Claude', S::label(['channel' => S::AI, 'name' => 'Claude']));
        $this->assertSame('Directo', S::label(['channel' => S::DIRECT, 'name' => null]));
    }
}
