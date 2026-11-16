@extends('layouts.admin')

@section('title', $item->exists ? 'Editar noticia' : 'Nueva noticia')

@section('content')
    <form method="POST" enctype="multipart/form-data" class="panel form-stack" novalidate
          action="{{ $item->exists ? route('admin.news.update', $item) : route('admin.news.store') }}">
        @csrf
        @if ($item->exists) @method('PUT') @endif

        <label class="field @error('title') has-error @enderror">
            <span>Título</span>
            <input type="text" name="title" value="{{ old('title', $item->title) }}" required maxlength="180">
            @error('title')<span class="field-error">{{ $message }}</span>@enderror
        </label>

        <div class="grid-2">
            <label class="field @error('tag') has-error @enderror">
                <span>Etiqueta <small class="muted">(opcional: Recorrido, Evento, Comunicado…)</small></span>
                <input type="text" name="tag" value="{{ old('tag', $item->tag) }}" maxlength="40">
                @error('tag')<span class="field-error">{{ $message }}</span>@enderror
            </label>
            <label class="field @error('published_at') has-error @enderror">
                <span>Fecha de publicación <small class="muted">(vacío = ahora; una fecha futura la programa)</small></span>
                <input type="datetime-local" name="published_at" value="{{ old('published_at', optional($item->published_at)->format('Y-m-d\TH:i')) }}">
                @error('published_at')<span class="field-error">{{ $message }}</span>@enderror
            </label>
        </div>

        <label class="field @error('summary') has-error @enderror">
            <span>Resumen <small class="muted">(aparece en el listado, en Google y al compartir en redes; máximo 500 caracteres)</small></span>
            <textarea name="summary" rows="3" maxlength="500" style="min-height: 80px">{{ old('summary', $item->summary) }}</textarea>
            @error('summary')<span class="field-error">{{ $message }}</span>@enderror
        </label>

        <label class="field @error('body') has-error @enderror">
            <span>Contenido <small class="muted">(separe los párrafos con una línea en blanco)</small></span>
            <textarea name="body" required>{{ old('body', $item->body) }}</textarea>
            @error('body')<span class="field-error">{{ $message }}</span>@enderror
        </label>

        <div class="grid-2">
            <div class="field @error('image') has-error @enderror">
                <label for="news-image">Imagen <small class="muted">(JPG, PNG o WEBP, máximo 4 MB)</small></label>
                <input type="file" id="news-image" name="image" accept="image/jpeg,image/png,image/webp">
                @error('image')<span class="field-error">{{ $message }}</span>@enderror
                @if ($item->image_path)
                    <img src="{{ asset('storage/'.$item->image_path) }}" alt="" class="thumb">
                    <label class="check"><input type="checkbox" name="remove_image" value="1"> Quitar la imagen actual</label>
                @endif
            </div>
            <label class="field @error('image_caption') has-error @enderror">
                <span>Pie de foto <small class="muted">(también se usa como texto alternativo)</small></span>
                <input type="text" name="image_caption" value="{{ old('image_caption', $item->image_caption) }}" maxlength="180">
                @error('image_caption')<span class="field-error">{{ $message }}</span>@enderror
            </label>
        </div>

        <label class="check">
            <input type="hidden" name="published" value="0">
            <input type="checkbox" name="published" value="1" @checked(old('published', $item->published))> Publicada (si se desmarca queda como borrador)
        </label>

        <p class="muted small">Recuerde: Laura se presenta como empresaria. No use las palabras “candidata” ni “campaña” hasta que sea oficial.</p>

        <div class="form-actions">
            <a href="{{ route('admin.news.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
@endsection
