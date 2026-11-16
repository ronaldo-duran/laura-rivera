<?php

namespace App\Support\Analytics;

/**
 * Clasifica de dónde llega una visita: asistentes de IA, buscadores,
 * redes sociales, campañas (utm), otros sitios o tráfico directo.
 */
class SourceClassifier
{
    public const AI = 'ia';

    public const SEARCH = 'buscador';

    public const SOCIAL = 'red_social';

    public const CAMPAIGN = 'campana';

    public const REFERRAL = 'referido';

    public const DIRECT = 'directo';

    public const INTERNAL = 'interno';

    public const BOT = 'bot';

    public const LABELS = [
        self::AI => 'Asistentes de IA',
        self::SEARCH => 'Buscadores',
        self::SOCIAL => 'Redes sociales',
        self::CAMPAIGN => 'Campañas (utm)',
        self::REFERRAL => 'Otros sitios',
        self::DIRECT => 'Directo',
        self::INTERNAL => 'Navegación interna',
        self::BOT => 'Rastreadores',
    ];

    /** dominio => nombre. Se compara el dominio exacto o cualquier subdominio. */
    public const AI_HOSTS = [
        'chatgpt.com' => 'ChatGPT',
        'chat.openai.com' => 'ChatGPT',
        'openai.com' => 'ChatGPT',
        'claude.ai' => 'Claude',
        'claude.com' => 'Claude',
        'anthropic.com' => 'Claude',
        'gemini.google.com' => 'Gemini',
        'bard.google.com' => 'Gemini',
        'aistudio.google.com' => 'Gemini',
        'perplexity.ai' => 'Perplexity',
        'copilot.microsoft.com' => 'Copilot',
        'copilot.cloud.microsoft' => 'Copilot',
        'meta.ai' => 'Meta AI',
        'chat.deepseek.com' => 'DeepSeek',
        'deepseek.com' => 'DeepSeek',
        'grok.com' => 'Grok',
        'x.ai' => 'Grok',
        'chat.mistral.ai' => 'Mistral',
        'you.com' => 'You.com',
        'phind.com' => 'Phind',
        'poe.com' => 'Poe',
    ];

    /** Valores de utm_source que agregan los asistentes de IA a sus enlaces. */
    public const AI_UTM = [
        'chatgpt.com' => 'ChatGPT', 'chatgpt' => 'ChatGPT', 'openai' => 'ChatGPT',
        'claude' => 'Claude', 'claude.ai' => 'Claude',
        'gemini' => 'Gemini', 'perplexity' => 'Perplexity', 'perplexity.ai' => 'Perplexity',
        'copilot' => 'Copilot', 'copilot.com' => 'Copilot', 'meta.ai' => 'Meta AI', 'metaai' => 'Meta AI',
        'deepseek' => 'DeepSeek', 'grok' => 'Grok',
    ];

    public const SEARCH_HOSTS = [
        'bing.com' => 'Bing', 'duckduckgo.com' => 'DuckDuckGo', 'search.yahoo.com' => 'Yahoo',
        'yahoo.com' => 'Yahoo', 'yandex.ru' => 'Yandex', 'yandex.com' => 'Yandex', 'ecosia.org' => 'Ecosia',
        'search.brave.com' => 'Brave', 'baidu.com' => 'Baidu',
    ];

    public const SOCIAL_HOSTS = [
        'facebook.com' => 'Facebook', 'fb.com' => 'Facebook', 'fb.me' => 'Facebook',
        'instagram.com' => 'Instagram', 'threads.net' => 'Threads', 'threads.com' => 'Threads',
        'x.com' => 'X (Twitter)', 'twitter.com' => 'X (Twitter)', 't.co' => 'X (Twitter)',
        'linkedin.com' => 'LinkedIn', 'lnkd.in' => 'LinkedIn',
        'tiktok.com' => 'TikTok', 'youtube.com' => 'YouTube', 'youtu.be' => 'YouTube',
        'whatsapp.com' => 'WhatsApp', 'wa.me' => 'WhatsApp',
        't.me' => 'Telegram', 'telegram.org' => 'Telegram', 'pinterest.com' => 'Pinterest', 'reddit.com' => 'Reddit',
    ];

    /**
     * @return array{channel: string, name: ?string}
     */
    public static function classify(?string $referrerHost, ?string $utmSource = null, ?string $utmMedium = null, ?string $ownHost = null): array
    {
        $host = static::normalizeHost($referrerHost);
        $utm = $utmSource !== null ? mb_strtolower(trim($utmSource)) : null;

        // 1. Asistentes de IA (por dominio de origen o por la marca utm que agregan)
        if ($host && ($name = static::match($host, static::AI_HOSTS))) {
            return ['channel' => self::AI, 'name' => $name];
        }
        if ($utm && isset(static::AI_UTM[$utm])) {
            return ['channel' => self::AI, 'name' => static::AI_UTM[$utm]];
        }

        // 2. Campañas propias marcadas con utm
        if ($utm) {
            return ['channel' => self::CAMPAIGN, 'name' => mb_substr($utm.($utmMedium ? ' / '.mb_strtolower($utmMedium) : ''), 0, 60)];
        }

        if (! $host) {
            return ['channel' => self::DIRECT, 'name' => null];
        }

        if ($ownHost && static::sameSite($host, static::normalizeHost($ownHost))) {
            return ['channel' => self::INTERNAL, 'name' => null];
        }

        // 3. Buscadores (Google en todos sus dominios: google.com, google.com.co…)
        if (preg_match('/(^|\.)google\.[a-z]{2,3}(\.[a-z]{2})?$/', $host)) {
            return ['channel' => self::SEARCH, 'name' => 'Google'];
        }
        if ($name = static::match($host, static::SEARCH_HOSTS)) {
            return ['channel' => self::SEARCH, 'name' => $name];
        }

        if ($name = static::match($host, static::SOCIAL_HOSTS)) {
            return ['channel' => self::SOCIAL, 'name' => $name];
        }

        return ['channel' => self::REFERRAL, 'name' => mb_substr($host, 0, 60)];
    }

    /** Texto corto para guardar con voluntarios y donaciones. */
    public static function label(array $c): string
    {
        $base = self::LABELS[$c['channel']] ?? $c['channel'];

        return $c['name'] ? "{$base} · {$c['name']}" : $base;
    }

    public static function normalizeHost(?string $host): ?string
    {
        if ($host === null || trim($host) === '') {
            return null;
        }
        $host = mb_strtolower(trim($host));

        return preg_replace('/^www\./', '', $host);
    }

    private static function match(string $host, array $map): ?string
    {
        foreach ($map as $domain => $name) {
            if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                return $name;
            }
        }

        return null;
    }

    private static function sameSite(string $a, ?string $b): bool
    {
        return $b !== null && ($a === $b || str_ends_with($a, '.'.$b) || str_ends_with($b, '.'.$a));
    }
}
