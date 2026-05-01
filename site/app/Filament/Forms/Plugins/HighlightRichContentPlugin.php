<?php

namespace App\Filament\Forms\Plugins;

use App\Tiptap\LineHeight;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Js;

/**
 * Дополнительные инструменты для Filament RichEditor:
 *
 *  Тулбар:
 *   - textColorPanel       — Word-style инлайн-панель цвета текста (палитра + произвольный цвет, очистка цвета).
 *   - highlightPanel       — Word-style инлайн-панель цвета фона (палитра + произвольный, снять текущий, снять везде).
 *   - lineSpacingMenu      — компактное выпадающее меню междустрочного интервала.
 *   - blockquoteWithAuthor — «Цитата»: всплывающее окно с полем автора (необязательно), затем blockquote + «ёлочки».
 *
 *  PHP-side TipTap:
 *   - LineHeight (App\Tiptap\LineHeight) — добавляет глобальный атрибут line-height на paragraph/heading.
 *   - Highlight подменён в AppServiceProvider — App\Tiptap\Highlight (multicolor=true по-умолчанию,
 *     null-safe для старого контента и фронта).
 *
 *  JS-side TipTap:
 *   - rich-content-plugins/highlight              — multicolor highlight в браузере (заменяет дефолтный).
 *   - rich-content-plugins/line-height            — команды setLineHeight / unsetLineHeight.
 *   - rich-content-plugins/blockquote-auto-quotes — «ёлочки» при включении цитаты (расширяет Blockquote).
 */
class HighlightRichContentPlugin implements RichContentPlugin
{
    /** @var array<int, string> 16 предустановленных цветов для палитры. */
    public const TEXT_COLORS = [
        '#000000', '#374151', '#6B7280', '#9CA3AF',
        '#FFFFFF', '#DC2626', '#EA580C', '#D97706',
        '#16A34A', '#0891B2', '#2563EB', '#4F46E5',
        '#7C3AED', '#C026D3', '#DB2777', '#4682B4',
    ];

    /** @var array<int, string> 16 пастельных цветов для подсветки. */
    public const HIGHLIGHT_COLORS = [
        '#FEF3C7', '#FDE68A', '#FCD34D', '#FBBF24',
        '#FECACA', '#FCA5A5', '#F87171', '#EF4444',
        '#BBF7D0', '#86EFAC', '#4ADE80', '#22C55E',
        '#BFDBFE', '#93C5FD', '#60A5FA', '#3B82F6',
    ];

    /** @var array<string, array{label: string, value: string}> В меню показывается только value; label совпадает с value для совместимости. */
    public const LINE_SPACINGS = [
        '1' => ['label' => '1', 'value' => '1'],
        '1.15' => ['label' => '1.15', 'value' => '1.15'],
        '1.5' => ['label' => '1.5', 'value' => '1.5'],
        '2' => ['label' => '2', 'value' => '2'],
        '3' => ['label' => '3', 'value' => '3'],
    ];

    public static function make(): static
    {
        return app(static::class);
    }

    public function getTipTapPhpExtensions(): array
    {
        return [
            app(LineHeight::class),
        ];
    }

    public function getTipTapJsExtensions(): array
    {
        return [
            FilamentAsset::getScriptSrc('rich-content-plugins/highlight'),
            FilamentAsset::getScriptSrc('rich-content-plugins/line-height'),
            FilamentAsset::getScriptSrc('rich-content-plugins/blockquote-auto-quotes'),
        ];
    }

    public function getEditorTools(): array
    {
        /*
         * Важно: значения палитр/опций должны попадать в HTML-атрибут x-on:click="..." без сырых кавычек.
         * Обычный json_encode ломает атрибут (первый " в массиве закрывает кавычки HTML) — Alpine не выполняет обработчик.
         * Js::from() даёт JSON.parse('…') с hex-экранированием — как у штатных Filament Action-аргументов.
         */
        $textColorsJs = Js::from(array_values(self::TEXT_COLORS))->toHtml();
        $highlightColorsJs = Js::from(array_values(self::HIGHLIGHT_COLORS))->toHtml();
        $spacingOptionsJs = Js::from(array_values(self::LINE_SPACINGS))->toHtml();

        return [
            RichEditorTool::make('textColorPanel')
                ->label('Цвет текста')
                ->activeKey('textColor')
                ->jsHandler('window.zmRichEditor?.openTextColorPanel($event, $getEditor(), '.$textColorsJs.')')
                ->icon(Heroicon::Swatch),

            RichEditorTool::make('highlightPanel')
                ->label('Цвет выделения')
                ->activeKey('highlight')
                ->jsHandler('window.zmRichEditor?.openHighlightPanel($event, $getEditor(), '.$highlightColorsJs.')')
                ->icon(Heroicon::PaintBrush),

            RichEditorTool::make('lineSpacingMenu')
                ->label('Междустрочный интервал')
                ->jsHandler('window.zmRichEditor?.openLineSpacingPanel($event, $getEditor(), '.$spacingOptionsJs.')')
                ->icon(Heroicon::ArrowsUpDown),

            RichEditorTool::make('blockquoteWithAuthor')
                ->label('Цитата')
                ->activeKey('blockquote')
                ->jsHandler('window.zmRichEditor?.openBlockquoteAuthorModal($event, $getEditor())')
                ->icon(Heroicon::ChatBubbleBottomCenterText),
        ];
    }

    public function getEditorActions(): array
    {
        return [];
    }
}
