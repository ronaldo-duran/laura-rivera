<?php

namespace Tests\Unit\Analytics;

use App\Support\Analytics\UserAgent;
use PHPUnit\Framework\TestCase;

class UserAgentTest extends TestCase
{
    public function test_mobile_android_chrome(): void
    {
        $ua = UserAgent::parse('Mozilla/5.0 (Linux; Android 14; SM-A546E) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36');
        $this->assertSame(['movil', 'Chrome', 'Android', false], [$ua['device'], $ua['browser'], $ua['os'], $ua['is_bot']]);
    }

    public function test_iphone_safari_and_desktop_edge(): void
    {
        $ios = UserAgent::parse('Mozilla/5.0 (iPhone; CPU iPhone OS 18_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.1 Mobile/15E148 Safari/604.1');
        $this->assertSame(['movil', 'Safari', 'iOS'], [$ios['device'], $ios['browser'], $ios['os']]);

        $edge = UserAgent::parse('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36 Edg/130.0.0.0');
        $this->assertSame(['escritorio', 'Edge', 'Windows'], [$edge['device'], $edge['browser'], $edge['os']]);
    }

    public function test_ipad_is_tablet(): void
    {
        $this->assertSame('tablet', UserAgent::parse('Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Safari/604.1')['device']);
    }

    public function test_ai_crawlers_are_detected(): void
    {
        foreach ([
            'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.2; +https://openai.com/gptbot)' => 'GPTBot (OpenAI)',
            'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; ClaudeBot/1.0; +claudebot@anthropic.com)' => 'ClaudeBot (Anthropic)',
            'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; ChatGPT-User/1.0; +https://openai.com/bot' => 'ChatGPT (consulta de usuario)',
            'Mozilla/5.0 (compatible; PerplexityBot/1.0; +https://perplexity.ai/perplexitybot)' => 'PerplexityBot',
            'meta-externalagent/1.1 (+https://developers.facebook.com/docs/sharing/webmasters/crawler)' => 'Meta AI',
        ] as $agent => $name) {
            $ua = UserAgent::parse($agent);
            $this->assertTrue($ua['is_bot'], $agent);
            $this->assertTrue($ua['ai_bot'], $agent);
            $this->assertSame($name, $ua['bot_name']);
        }
    }

    public function test_search_bots_and_scripts_are_bots_but_not_ai(): void
    {
        $g = UserAgent::parse('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
        $this->assertTrue($g['is_bot']);
        $this->assertFalse($g['ai_bot']);

        $this->assertTrue(UserAgent::parse('curl/8.5.0')['is_bot']);
        $this->assertTrue(UserAgent::parse('')['is_bot']);
        $this->assertTrue(UserAgent::parse('facebookexternalhit/1.1')['is_bot']);
    }
}
