<?php

namespace App\Support;

use DOMDocument;
use DOMElement;

/**
 * На публичном сайте добавляет «ёлочки» к первому смысловому абзацу в blockquote,
 * если в тексте ещё нет U+00AB (как в шаблоне Diplom_Site/blog-post.html).
 */
final class BlockquoteGuillemets
{
    public static function ensureInHtml(string $html): string
    {
        if ($html === '' || ! str_contains($html, 'blockquote')) {
            return $html;
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $wrapped = '<?xml encoding="UTF-8"><html><body><div id="zm-root">'.$html.'</div></body></html>';
        if (! @$dom->loadHTML($wrapped, LIBXML_HTML_NODEFDTD | LIBXML_NOERROR)) {
            libxml_clear_errors();

            return $html;
        }
        libxml_clear_errors();

        $root = $dom->getElementById('zm-root');
        if (! $root instanceof DOMElement) {
            return $html;
        }

        foreach (iterator_to_array($root->getElementsByTagName('blockquote'), false) as $bq) {
            if (! $bq instanceof DOMElement) {
                continue;
            }
            $quoteP = self::firstQuoteParagraph($bq);
            if (! $quoteP instanceof DOMElement) {
                continue;
            }
            $plain = trim($quoteP->textContent ?? '');
            if ($plain === '' || str_starts_with($plain, "\u{00AB}")) {
                continue;
            }
            if ($quoteP->firstChild !== null) {
                $quoteP->insertBefore($dom->createTextNode("\u{00AB}"), $quoteP->firstChild);
            } else {
                $quoteP->appendChild($dom->createTextNode("\u{00AB}"));
            }
            $quoteP->appendChild($dom->createTextNode("\u{00BB}"));
        }

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return $out !== '' ? $out : $html;
    }

    /**
     * Первый непустой абзац цитаты, не похожий на строку-подпись (начинается с тире).
     */
    private static function firstQuoteParagraph(DOMElement $bq): ?DOMElement
    {
        foreach ($bq->childNodes as $child) {
            if (! $child instanceof DOMElement || strtolower($child->nodeName) !== 'p') {
                continue;
            }
            $t = trim($child->textContent ?? '');
            if ($t === '') {
                continue;
            }
            if (preg_match('/^[\x{2014}\x{2013}\-]/u', $t)) {
                continue;
            }

            return $child;
        }

        return null;
    }
}
