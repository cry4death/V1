<?php

namespace App\Filament\Support;

use Closure;
use Filament\Forms\Components\FileUpload;
use League\Flysystem\UnableToCheckFileExistence;
use Throwable;

/**
 * URL для превью FileUpload: относительный путь вместо абсолютного APP_URL,
 * чтобы fetch() в FilePond не ломался при 127.0.0.1 vs localhost.
 */
final class LocalPublicFileUpload
{
    /**
     * @return Closure(FileUpload, string, string|array|null): ?array
     */
    public static function uploadedFileUsing(): Closure
    {
        return function (FileUpload $component, string $file, string | array | null $storedFileNames): ?array {
            /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
            $storage = $component->getDisk();
            $shouldFetchFileInformation = $component->shouldFetchFileInformation();

            if ($shouldFetchFileInformation) {
                try {
                    if (! $storage->exists($file)) {
                        return null;
                    }
                } catch (UnableToCheckFileExistence) {
                    return null;
                }
            }

            $url = null;

            if ($component->getVisibility() === 'private') {
                try {
                    $url = $storage->temporaryUrl(
                        $file,
                        now()->addMinutes(30)->endOfHour(),
                    );
                } catch (Throwable) {
                }
            }

            if (blank($url)) {
                $url = $storage->url($file);
            }

            $diskName = $component->getDiskName();
            if (
                is_string($url)
                && in_array($diskName, ['public', 'public_assets'], true)
                && preg_match('#^https?://#i', $url)
            ) {
                $path = parse_url($url, PHP_URL_PATH);
                $query = parse_url($url, PHP_URL_QUERY);
                if (is_string($path) && $path !== '') {
                    $url = $path.($query ? '?'.$query : '');
                }
            }

            return [
                'name' => ($component->isMultiple() ? ($storedFileNames[$file] ?? null) : $storedFileNames) ?? basename($file),
                'size' => $shouldFetchFileInformation ? $storage->size($file) : 0,
                'type' => $shouldFetchFileInformation ? $storage->mimeType($file) : null,
                'url' => $url,
            ];
        };
    }
}
