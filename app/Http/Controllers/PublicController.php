<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        return view('home', [
            'news' => News::published()->latest('published_at')->take(3)->get(),
        ]);
    }

    public function about(): View
    {
        return view('about');
    }

    public function proposals(): View
    {
        return view('proposals');
    }

    public function newsIndex(): View
    {
        return view('news.index', [
            'news' => News::published()->latest('published_at')->paginate(9),
        ]);
    }

    public function newsShow(News $news): View
    {
        abort_unless($news->published && (! $news->published_at || $news->published_at->isPast()), 404);

        return view('news.show', [
            'item' => $news,
            'related' => News::published()->whereKeyNot($news->id)->latest('published_at')->take(3)->get(),
        ]);
    }

    public function terms(): View
    {
        return view('legal.terms');
    }

    public function privacy(): View
    {
        return view('legal.privacy');
    }
}
