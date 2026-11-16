<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewsRequest;
use App\Models\News;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class NewsController extends Controller
{
    private const DISK = 'public';

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        return view('admin.news.index', [
            'news' => News::query()
                ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                    ->where('title', 'like', "%{$q}%")->orWhere('tag', 'like', "%{$q}%")))
                ->orderByDesc('published_at')->orderByDesc('id')
                ->paginate(20)->withQueryString(),
            'q' => $q,
        ]);
    }

    public function create(): View
    {
        return view('admin.news.form', ['item' => new News(['published' => true])]);
    }

    public function store(NewsRequest $request): RedirectResponse
    {
        $item = new News;
        $this->fill($item, $request);
        $item->author_id = $request->user()->id;
        $item->save();

        return redirect()->route('admin.news.index')->with('status', 'Noticia creada.');
    }

    public function edit(News $news): View
    {
        return view('admin.news.form', ['item' => $news]);
    }

    public function update(NewsRequest $request, News $news): RedirectResponse
    {
        $this->fill($news, $request);
        $news->save();

        return redirect()->route('admin.news.index')->with('status', 'Noticia actualizada.');
    }

    public function destroy(News $news): RedirectResponse
    {
        $this->deleteImage($news->image_path);
        $news->delete();

        return redirect()->route('admin.news.index')->with('status', 'Noticia eliminada.');
    }

    private function fill(News $item, NewsRequest $request): void
    {
        $data = $request->validated();

        $item->fill([
            'title' => $data['title'],
            'tag' => $data['tag'] ?? null,
            'summary' => $data['summary'] ?? null,
            'body' => $data['body'],
            'image_caption' => $data['image_caption'] ?? null,
            'published' => $data['published'],
            'published_at' => $data['published_at'] ?? ($data['published'] ? ($item->published_at ?? now()) : null),
        ]);

        if ($request->hasFile('image')) {
            $this->deleteImage($item->image_path);
            $item->image_path = $request->file('image')->store('noticias', self::DISK);
        } elseif ($data['remove_image'] && $item->image_path) {
            $this->deleteImage($item->image_path);
            $item->image_path = null;
        }
    }

    private function deleteImage(?string $path): void
    {
        // Solo borramos lo que subió el panel (las imágenes de ejemplo viven en /public/assets)
        if ($path && str_starts_with($path, 'noticias/')) {
            Storage::disk(self::DISK)->delete($path);
        }
    }
}
