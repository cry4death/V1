<?php

namespace App\Tiptap;

use Tiptap\Core\Extension;
use Tiptap\Utils\InlineStyle;

/**
 * Глобальный атрибут line-height для блочных элементов (paragraph, heading).
 * Формирует style="line-height: …" при сериализации в HTML.
 */
class LineHeight extends Extension
{
    public static $name = 'lineHeight';

    public function addOptions()
    {
        return [
            'types' => ['paragraph', 'heading'],
        ];
    }

    public function addGlobalAttributes()
    {
        return [
            [
                'types' => $this->options['types'],
                'attributes' => [
                    'lineHeight' => [
                        'default' => null,
                        'parseHTML' => fn ($DOMNode) => InlineStyle::getAttribute($DOMNode, 'line-height') ?: null,
                        'renderHTML' => function ($attributes) {
                            $value = is_object($attributes)
                                ? ($attributes->lineHeight ?? null)
                                : ($attributes['lineHeight'] ?? null);

                            if (! filled($value)) {
                                return null;
                            }

                            return ['style' => "line-height: {$value}"];
                        },
                    ],
                ],
            ],
        ];
    }
}
