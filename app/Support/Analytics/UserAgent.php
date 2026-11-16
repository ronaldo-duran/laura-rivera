<?php

namespace App\Support\Analytics;

/**
 * Lectura ligera del User-Agent: tipo de dispositivo, navegador, sistema y
 * rastreadores (en especial los de IA, que importan para el posicionamiento).
 */
class UserAgent
{
    /** patrón => [nombre, ¿es de IA?]. El orden importa: lo más específico primero. */
    public const BOTS = [
        'ChatGPT-User' => ['ChatGPT (consulta de usuario)', true],
        'OAI-SearchBot' => ['OpenAI SearchBot', true],
        'GPTBot' => ['GPTBot (OpenAI)', true],
        'Claude-User' => ['Claude (consulta de usuario)', true],
        'Claude-SearchBot' => ['Claude SearchBot', true],
        'ClaudeBot' => ['ClaudeBot (Anthropic)', true],
        'anthropic-ai' => ['Anthropic', true],
        'Perplexity-User' => ['Perplexity (consulta de usuario)', true],
        'PerplexityBot' => ['PerplexityBot', true],
        'Google-Extended' => ['Google-Extended (Gemini)', true],
        'Google-CloudVertexBot' => ['Google Vertex AI', true],
        'GoogleOther' => ['GoogleOther', true],
        'meta-externalagent' => ['Meta AI', true],
        'meta-externalfetcher' => ['Meta AI (consulta)', true],
        'FacebookBot' => ['Meta (FacebookBot)', true],
        'Applebot-Extended' => ['Apple Intelligence', true],
        'MistralAI-User' => ['Mistral', true],
        'DeepSeekBot' => ['DeepSeek', true],
        'Bytespider' => ['Bytespider (ByteDance)', true],
        'CCBot' => ['Common Crawl', true],
        'cohere-ai' => ['Cohere', true],
        'Amazonbot' => ['Amazonbot', true],
        'Googlebot' => ['Googlebot', false],
        'bingbot' => ['Bingbot', false],
        'Applebot' => ['Applebot', false],
        'DuckDuckBot' => ['DuckDuckBot', false],
        'YandexBot' => ['YandexBot', false],
        'facebookexternalhit' => ['Vista previa de Facebook/WhatsApp', false],
        'WhatsApp' => ['Vista previa de WhatsApp', false],
        'Twitterbot' => ['Vista previa de X', false],
        'LinkedInBot' => ['Vista previa de LinkedIn', false],
        'TelegramBot' => ['Vista previa de Telegram', false],
        'Slackbot' => ['Vista previa de Slack', false],
    ];

    /**
     * @return array{device: string, browser: ?string, os: ?string, is_bot: bool, bot_name: ?string, ai_bot: bool}
     */
    public static function parse(?string $ua): array
    {
        $ua = (string) $ua;

        foreach (static::BOTS as $needle => [$name, $ai]) {
            if (stripos($ua, $needle) !== false) {
                return static::bot($name, $ai);
            }
        }
        if ($ua === '' || preg_match('/bot\b|crawl|spider|slurp|curl\/|wget|python-requests|python-httpx|go-http-client|okhttp|axios|node-fetch|headlesschrome|phantomjs|lighthouse|scrapy|httpclient|java\//i', $ua)) {
            return static::bot($ua === '' ? 'Sin identificar' : 'Otro rastreador', false);
        }

        return [
            'device' => static::device($ua),
            'browser' => static::browser($ua),
            'os' => static::os($ua),
            'is_bot' => false,
            'bot_name' => null,
            'ai_bot' => false,
        ];
    }

    private static function bot(string $name, bool $ai): array
    {
        return ['device' => 'bot', 'browser' => null, 'os' => null, 'is_bot' => true, 'bot_name' => $name, 'ai_bot' => $ai];
    }

    private static function device(string $ua): string
    {
        if (preg_match('/iPad|Tablet|PlayBook|Silk|(Android(?!.*Mobile))/i', $ua)) {
            return 'tablet';
        }
        if (preg_match('/Mobi|iPhone|iPod|Android|Windows Phone|Opera Mini|IEMobile/i', $ua)) {
            return 'movil';
        }

        return 'escritorio';
    }

    private static function browser(string $ua): ?string
    {
        return match (true) {
            (bool) preg_match('/FBAN|FBAV|FB_IAB/', $ua) => 'Facebook (app)',
            (bool) preg_match('/Instagram/', $ua) => 'Instagram (app)',
            (bool) preg_match('/Edg\//', $ua) => 'Edge',
            (bool) preg_match('/OPR\/|Opera/', $ua) => 'Opera',
            (bool) preg_match('/SamsungBrowser/', $ua) => 'Samsung Internet',
            (bool) preg_match('/Firefox\/|FxiOS/', $ua) => 'Firefox',
            (bool) preg_match('/Chrome\/|CriOS/', $ua) => 'Chrome',
            (bool) preg_match('/Safari\//', $ua) => 'Safari',
            default => null,
        };
    }

    private static function os(string $ua): ?string
    {
        return match (true) {
            (bool) preg_match('/iPhone|iPad|iPod/', $ua) => 'iOS',
            (bool) preg_match('/Android/', $ua) => 'Android',
            (bool) preg_match('/Windows/', $ua) => 'Windows',
            (bool) preg_match('/Mac OS X|Macintosh/', $ua) => 'macOS',
            (bool) preg_match('/CrOS/', $ua) => 'ChromeOS',
            (bool) preg_match('/Linux/', $ua) => 'Linux',
            default => null,
        };
    }
}
