<?php

namespace App\Filament\Resources\Directions\Concerns;

trait NormalizesDirectionPayload
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeDirectionPayload(array $data): array
    {
        $details = $data['details'] ?? [];
        if (! is_array($details)) {
            $details = [];
        }

        foreach (['when_list', 'treat_list'] as $key) {
            if (! isset($details[$key])) {
                continue;
            }
            $raw = $details[$key];
            if (is_string($raw)) {
                $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
                $details[$key] = array_values(array_filter(
                    array_map(static fn (string $line): string => trim($line), $lines),
                    static fn (string $line): bool => $line !== '',
                ));
            } elseif (is_array($raw)) {
                $details[$key] = array_values(array_filter(
                    array_map(static fn ($v) => is_string($v) ? trim($v) : '', $raw),
                    static fn (string $v): bool => $v !== '',
                ));
            } else {
                $details[$key] = [];
            }
        }

        if (! empty($details['faq']) && is_array($details['faq'])) {
            $details['faq'] = array_values(array_filter(
                $details['faq'],
                static function ($item): bool {
                    if (! is_array($item)) {
                        return false;
                    }

                    return filled(trim((string) ($item['question'] ?? '')));
                },
            ));
        } else {
            $details['faq'] = [];
        }

        foreach (['general', 'when_subtitle', 'conclusion', 'treat_subtitle'] as $textKey) {
            if (array_key_exists($textKey, $details) && $details[$textKey] === null) {
                $details[$textKey] = '';
            }
        }

        if (! empty($data['image']) && is_string($data['image'])) {
            $details['banner'] = $data['image'];
        }

        $data['details'] = $details;

        return $data;
    }
}
