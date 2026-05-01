/**
 * Минимальная подмазка под Filament v5 RichEditor (TipTap):
 *  - подсветка выделенной картинки в редакторе (визуально показывает резайз-рамку
 *    после клика; сами resize-рукоятки рисует Filament).
 *
 * Цвет текста, цвет выделения и междустрочный интервал теперь реализованы
 * штатными Filament Action-модалками (см. App\Filament\Forms\Plugins\HighlightRichContentPlugin).
 */
(function () {
    'use strict';

    document.addEventListener('click', function (e) {
        var target = e.target;
        var clickedImg = (target && target.tagName === 'IMG' && target.closest('.fi-fo-rich-editor')) ? target : null;
        document.querySelectorAll('.fi-fo-rich-editor img.is-selected').forEach(function (img) {
            if (img !== clickedImg) img.classList.remove('is-selected');
        });
        if (clickedImg) {
            clickedImg.classList.add('is-selected');
        }
    });
})();
