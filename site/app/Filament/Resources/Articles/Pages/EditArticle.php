<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['slug_locked'] = true;

        $cover = $data['cover_image'] ?? null;
        if (! is_string($cover) || $cover === '') {
            return $data;
        }

        if (! str_contains($cover, '/') && ! str_contains($cover, '\\')) {
            $prefixed = 'images/blog/'.$cover;
            if (Storage::disk('public_assets')->exists($prefixed)) {
                $data['cover_image'] = $prefixed;
            }
        }

        return $data;
    }
}
