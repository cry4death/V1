/**
 * Подсказки и UX для Filament FileUpload (FilePond): удаление, скачивание, открытие и др.
 */
(function () {
    'use strict';

    var BUTTON_TITLES = {
        'filepond--action-remove-item':
            'Удалить файл с поля. После удаления можно загрузить другой файл.',
        'filepond--action-abort-item-load': 'Прервать загрузку файла.',
        'filepond--action-revert-item-processing': 'Отменить обработку изображения.',
        'filepond--action-retry-item-load': 'Повторить загрузку.',
        'filepond--action-process-item': 'Загрузить выбранный файл на сервер.',
    };

    /** Ссылки «Скачать» / «Открыть» в Filament — классы `filepond--download-icon` и `filepond--open-icon`. */
    var EXTRA_CLASS_TITLES = [
        ['filepond--download-icon', 'Скачать файл на устройство'],
        ['filepond--open-icon', 'Открыть файл в новой вкладке (просмотр)'],
        ['filepond--action-download-item', 'Скачать файл на устройство'],
        ['filepond--action-open-item', 'Открыть файл в новой вкладке (просмотр)'],
    ];

    function markTitle(el, title) {
        if (el.getAttribute('data-zm-file-title') === '1') {
            return;
        }
        el.setAttribute('title', title);
        el.setAttribute('data-zm-file-title', '1');
    }

    function applyButtonTitles() {
        document.querySelectorAll('.fi-fo-file-upload button[class*="filepond--action-"]').forEach(function (btn) {
            if (btn.getAttribute('data-zm-file-title') === '1') {
                return;
            }
            for (var cls in BUTTON_TITLES) {
                if (Object.prototype.hasOwnProperty.call(BUTTON_TITLES, cls) && btn.classList.contains(cls)) {
                    markTitle(btn, BUTTON_TITLES[cls]);
                    return;
                }
            }
        });
    }

    function applyDownloadOpenTitles() {
        EXTRA_CLASS_TITLES.forEach(function (pair) {
            var fragment = pair[0];
            var title = pair[1];
            document
                .querySelectorAll('.fi-fo-file-upload [class*="' + fragment + '"]')
                .forEach(function (el) {
                    markTitle(el, title);
                });
        });
    }

    function applyAll() {
        applyButtonTitles();
        applyDownloadOpenTitles();
    }

    var scheduled;
    function schedule() {
        clearTimeout(scheduled);
        scheduled = setTimeout(applyAll, 80);
    }

    if (typeof MutationObserver !== 'undefined') {
        new MutationObserver(schedule).observe(document.body, {
            childList: true,
            subtree: true,
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyAll);
    } else {
        applyAll();
    }
})();
