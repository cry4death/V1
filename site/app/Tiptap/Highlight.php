<?php

namespace App\Tiptap;

use Tiptap\Marks\Highlight as BaseHighlight;
use Tiptap\Utils\InlineStyle;

/**
 * Безопасная мультицветная версия Highlight:
 *  - всегда работает в режиме multicolor=true (иначе тег <mark> в выводе остаётся
 *    без data-color/style и браузер красит его дефолтным жёлтым);
 *  - корректно переваривает старый контент с <mark> без data-color (не падает
 *    в PHP 8.4 на «Undefined property: stdClass::$color»).
 */
class Highlight extends BaseHighlight
{
    public function addOptions()
    {
        return array_merge(parent::addOptions(), [
            'multicolor' => true,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function addAttributes(): array
    {
        return [
            'color' => [
                'default' => null,
                'parseHTML' => function ($DOMNode) {
                    if ($color = $DOMNode->getAttribute('data-color')) {
                        return $color;
                    }

                    return InlineStyle::getAttribute($DOMNode, 'background-color') ?: null;
                },
                'renderHTML' => function ($attributes) {
                    $color = is_object($attributes) ? ($attributes->color ?? null) : ($attributes['color'] ?? null);

                    if (! filled($color)) {
                        return null;
                    }

                    return [
                        'data-color' => $color,
                        'style' => "background-color: {$color}",
                    ];
                },
            ],
        ];
    }
}
