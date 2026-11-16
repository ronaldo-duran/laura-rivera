<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $static = [
            ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => route('about'), 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['loc' => route('proposals'), 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['loc' => route('news.index'), 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => route('volunteers.create'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => route('terms'), 'priority' => '0.2', 'changefreq' => 'yearly'],
            ['loc' => route('privacy'), 'priority' => '0.2', 'changefreq' => 'yearly'],
        ];

        $news = News::published()->latest('published_at')->get(['slug', 'updated_at']);

        return response()
            ->view('seo.sitemap', compact('static', 'news'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /login',
            '',
            '# Asistentes de IA: bienvenidos a leer el contenido público',
            'User-agent: GPTBot',
            'Allow: /',
            'User-agent: OAI-SearchBot',
            'Allow: /',
            'User-agent: ChatGPT-User',
            'Allow: /',
            'User-agent: ClaudeBot',
            'Allow: /',
            'User-agent: Claude-User',
            'Allow: /',
            'User-agent: Claude-SearchBot',
            'Allow: /',
            'User-agent: Google-Extended',
            'Allow: /',
            'User-agent: PerplexityBot',
            'Allow: /',
            'User-agent: Meta-ExternalAgent',
            'Allow: /',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function llms(): Response
    {
        $news = News::published()->latest('published_at')->take(10)->get();

        return response()
            ->view('seo.llms', compact('news'))
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
