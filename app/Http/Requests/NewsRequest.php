<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // el acceso lo controla el middleware de permisos de la ruta
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim((string) $this->input('title')),
            'tag' => filled($this->input('tag')) ? trim((string) $this->input('tag')) : null,
            'published' => $this->boolean('published'),
            'remove_image' => $this->boolean('remove_image'),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'tag' => ['nullable', 'string', 'max:40'],
            'summary' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:60000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=6000,max_height=6000'],
            'image_caption' => ['nullable', 'string', 'max:180'],
            'published' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'remove_image' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'tag' => 'etiqueta',
            'summary' => 'resumen',
            'body' => 'contenido',
            'image' => 'imagen',
            'image_caption' => 'pie de foto',
            'published_at' => 'fecha de publicación',
        ];
    }

    public function messages(): array
    {
        return [
            'image.max' => 'La imagen no puede pesar más de 4 MB.',
            'image.mimes' => 'La imagen debe ser JPG, PNG o WEBP.',
        ];
    }
}
