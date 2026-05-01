/**
 * Инлайн-панели Filament RichEditor (TipTap): цвет текста, выделение, междустрочный интервал.
 * Панель position:fixed, укладывается в viewport; после выбора цвета/интервала/сброса закрывается.
 */
(function () {
    'use strict';

    const PANEL_CLASS = 'zm-rte-panel';
    const PANEL_ATTR = 'data-zm-rte-panel';
    const cleanupFns = [];

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (ch) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
            }[ch];
        });
    }

    function closeAll() {
        while (cleanupFns.length) {
            try {
                cleanupFns.pop()();
            } catch (_) {
                /* no-op */
            }
        }
        document.querySelectorAll('[' + PANEL_ATTR + ']').forEach(function (panel) {
            panel.remove();
        });
    }

    /**
     * Фиксируем панель в окне просмотра: не уезжает вниз за край, при нехватке места — прокрутка внутри панели.
     */
    function fitPanelToViewport(panel, button) {
        var margin = 8;
        panel.style.position = 'fixed';
        panel.style.zIndex = '2147483646';
        panel.style.boxSizing = 'border-box';
        panel.style.maxHeight = 'calc(100vh - ' + margin * 2 + 'px)';
        panel.style.overflowY = 'auto';
        panel.style.overflowX = 'hidden';

        var rect = button.getBoundingClientRect();
        var vw = window.innerWidth;
        var vh = window.innerHeight;
        var pw = panel.offsetWidth || 248;
        var ph = panel.offsetHeight || 120;

        var left = rect.left;
        if (left + pw > vw - margin) {
            left = vw - pw - margin;
        }
        if (left < margin) {
            left = margin;
        }

        var topBelow = rect.bottom + 4;
        var topAbove = rect.top - ph - 4;
        var top;
        var roomBelow = vh - rect.bottom - margin - 4;
        var roomAbove = rect.top - margin - 4;

        if (ph <= roomBelow || roomBelow >= roomAbove) {
            top = topBelow;
            if (top + ph > vh - margin) {
                top = Math.max(margin, vh - margin - ph);
            }
        } else if (ph <= roomAbove) {
            top = topAbove;
        } else {
            top = Math.max(margin, Math.min(topBelow, vh - margin - ph));
        }

        panel.style.left = Math.round(left) + 'px';
        panel.style.top = Math.round(top) + 'px';
    }

    function scheduleFit(panel, button) {
        requestAnimationFrame(function () {
            fitPanelToViewport(panel, button);
            requestAnimationFrame(function () {
                fitPanelToViewport(panel, button);
            });
        });
    }

    function bindDismissers(panel, button, onClose) {
        var onDocClick = function (event) {
            if (panel.contains(event.target) || button.contains(event.target)) {
                return;
            }
            cleanup();
        };
        var onKey = function (event) {
            if (event.key === 'Escape') {
                cleanup();
            }
        };
        var onScrollOrResize = function () {
            if (!document.body.contains(panel)) {
                return;
            }
            fitPanelToViewport(panel, button);
        };
        var cleaned = false;
        var cleanup = function () {
            if (cleaned) {
                return;
            }
            cleaned = true;
            document.removeEventListener('mousedown', onDocClick, true);
            document.removeEventListener('keydown', onKey, true);
            window.removeEventListener('resize', onScrollOrResize);
            window.removeEventListener('scroll', onScrollOrResize, true);
            var idx = cleanupFns.indexOf(cleanup);
            if (idx !== -1) {
                cleanupFns.splice(idx, 1);
            }
            if (panel.parentNode) {
                panel.remove();
            }
            if (typeof onClose === 'function') {
                onClose();
            }
        };
        setTimeout(function () {
            document.addEventListener('mousedown', onDocClick, true);
            document.addEventListener('keydown', onKey, true);
            window.addEventListener('resize', onScrollOrResize);
            window.addEventListener('scroll', onScrollOrResize, true);
        }, 0);
        cleanupFns.push(cleanup);
        return cleanup;
    }

    function withEditorFocus(editor, callback) {
        if (!editor) {
            return;
        }
        try {
            var chain = editor.chain().focus();
            callback(chain);
        } catch (error) {
            console.error('[zmRichEditor] command failed:', error);
        }
    }

    function buildColorGrid(palette, currentColor, onPick) {
        var wrap = document.createElement('div');
        wrap.className = 'zm-rte-color-grid';
        palette.forEach(function (color) {
            var cell = document.createElement('button');
            cell.type = 'button';
            cell.className = 'zm-rte-color-swatch';
            cell.style.background = color;
            cell.setAttribute('aria-label', color);
            cell.title = color;
            if (currentColor && String(currentColor).toLowerCase() === String(color).toLowerCase()) {
                cell.classList.add('is-active');
            }
            cell.addEventListener('click', function (event) {
                event.preventDefault();
                onPick(color);
                closeAll();
            });
            wrap.appendChild(cell);
        });
        return wrap;
    }

    function buildCustomColorRow(currentColor, onPickInput, onPickCommit) {
        var wrap = document.createElement('label');
        wrap.className = 'zm-rte-custom-row';
        var initial =
            currentColor && /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(currentColor) ? currentColor : '#4682b4';
        wrap.innerHTML =
            '<span class="zm-rte-custom-row__label">Свой цвет</span>' +
            '<input type="color" value="' +
            escapeHtml(initial) +
            '">' +
            '<span class="zm-rte-custom-row__hint">палитра ОС</span>';
        var input = wrap.querySelector('input[type="color"]');
        input.addEventListener('input', function () {
            onPickInput(input.value);
        });
        input.addEventListener('change', function () {
            onPickCommit(input.value);
            closeAll();
        });
        return wrap;
    }

    function buildHeading(text) {
        var heading = document.createElement('div');
        heading.className = 'zm-rte-heading';
        heading.textContent = text;
        return heading;
    }

    function buildResetButton(label, onClick, modifierClass) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'zm-rte-reset' + (modifierClass ? ' ' + modifierClass : '');
        btn.textContent = label;
        btn.addEventListener('click', function (event) {
            event.preventDefault();
            onClick();
            closeAll();
        });
        return btn;
    }

    function removeAllMarks(editor, markName) {
        if (!editor) {
            return;
        }
        var view = editor.view;
        var state = view.state;
        var markType = state.schema.marks[markName];
        if (!markType) {
            return;
        }

        var tr = state.tr;
        state.doc.descendants(function (node, pos) {
            if (!node.isText) {
                return;
            }
            tr.removeMark(pos, pos + node.nodeSize, markType);
        });
        if (tr.docChanged) {
            view.dispatch(tr.scrollIntoView());
        }
    }

    /** Без явного line-height в документе считаем интервал «1». */
    function effectiveLineHeight(editor) {
        var lh =
            (editor.getAttributes('paragraph') || {}).lineHeight ||
            (editor.getAttributes('heading') || {}).lineHeight ||
            null;
        if (lh == null || lh === '') {
            return '1';
        }
        var s = String(lh).trim();
        if (s === '1.0') {
            return '1';
        }
        return s;
    }

    function showPanel(event, editor, builder, options) {
        if (!editor) {
            return;
        }
        var button =
            event && event.currentTarget
                ? event.currentTarget
                : event.target
                  ? event.target.closest('button')
                  : null;
        if (!button) {
            return;
        }
        if (button.dataset.zmRtePanelOpen === '1') {
            closeAll();
            return;
        }
        closeAll();
        button.dataset.zmRtePanelOpen = '1';

        var extra = (options && options.extraClass) || '';
        var menu = (options && options.menuClass) || '';
        var panel = document.createElement('div');
        panel.className = [PANEL_CLASS, menu, extra].filter(Boolean).join(' ');
        panel.setAttribute(PANEL_ATTR, '');
        builder(panel, editor);

        document.body.appendChild(panel);
        scheduleFit(panel, button);
        bindDismissers(panel, button, function () {
            delete button.dataset.zmRtePanelOpen;
        });
    }

    function openTextColorPanel(event, editor, palette) {
        showPanel(
            event,
            editor,
            function (panel) {
                var currentColor = (editor.getAttributes('textColor') || {})['data-color'] || null;
                panel.appendChild(buildHeading('Цвет текста'));
                panel.appendChild(
                    buildColorGrid(palette || [], currentColor, function (color) {
                        withEditorFocus(editor, function (chain) {
                            chain.setTextColor({ color: color }).run();
                        });
                    }),
                );
                panel.appendChild(
                    buildCustomColorRow(
                        currentColor,
                        function (color) {
                            withEditorFocus(editor, function (chain) {
                                chain.setTextColor({ color: color }).run();
                            });
                        },
                        function (color) {
                            withEditorFocus(editor, function (chain) {
                                chain.setTextColor({ color: color }).run();
                            });
                        },
                    ),
                );
                panel.appendChild(
                    buildResetButton('Сбросить цвет', function () {
                        withEditorFocus(editor, function (chain) {
                            chain.unsetTextColor().run();
                        });
                    }),
                );
            },
            { extraClass: 'zm-rte-panel--color-tools' },
        );
    }

    function openHighlightPanel(event, editor, palette) {
        showPanel(
            event,
            editor,
            function (panel) {
                var currentColor = (editor.getAttributes('highlight') || {}).color || null;
                panel.appendChild(buildHeading('Цвет выделения'));
                panel.appendChild(
                    buildColorGrid(palette || [], currentColor, function (color) {
                        withEditorFocus(editor, function (chain) {
                            chain.setHighlight({ color: color }).run();
                        });
                    }),
                );
                panel.appendChild(
                    buildCustomColorRow(
                        currentColor,
                        function (color) {
                            withEditorFocus(editor, function (chain) {
                                chain.setHighlight({ color: color }).run();
                            });
                        },
                        function (color) {
                            withEditorFocus(editor, function (chain) {
                                chain.setHighlight({ color: color }).run();
                            });
                        },
                    ),
                );
                panel.appendChild(
                    buildResetButton('Снять выделение с этого фрагмента', function () {
                        withEditorFocus(editor, function (chain) {
                            chain.unsetHighlight().run();
                        });
                    }),
                );
                panel.appendChild(
                    buildResetButton('Снять выделение со всего текста', function () {
                        removeAllMarks(editor, 'highlight');
                    }),
                );
            },
            { extraClass: 'zm-rte-panel--color-tools' },
        );
    }

    function openLineSpacingPanel(event, editor, options) {
        showPanel(
            event,
            editor,
            function (panel) {
                var eff = effectiveLineHeight(editor);
                panel.appendChild(buildHeading('Междустрочный интервал'));
                (options || []).forEach(function (option) {
                    var val = String(option.value);
                    var item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'zm-rte-menu-item';
                    item.textContent = val;
                    item.setAttribute('aria-label', 'Интервал ' + val);
                    if (eff === val) {
                        item.classList.add('is-active');
                    }
                    item.addEventListener('click', function (e) {
                        e.preventDefault();
                        withEditorFocus(editor, function (chain) {
                            chain.setLineHeight(val).run();
                        });
                        closeAll();
                    });
                    panel.appendChild(item);
                });
            },
            { menuClass: 'zm-rte-panel--menu zm-rte-panel--spacing-menu' },
        );
    }

    function normalizeAuthorLine(text) {
        var t = String(text).trim();
        if (!t) {
            return '';
        }
        if (/^[\u2014\u2013\u002D]\s?/.test(t)) {
            return t;
        }
        return '\u2014 ' + t;
    }

    function isLikelyAttributionParagraph(node) {
        if (!node || node.type.name !== 'paragraph') {
            return false;
        }
        var t = String(node.textContent || '')
            .replace(/\s+/g, ' ')
            .trim();
        if (!t) {
            return false;
        }
        return /^[\u2014\u2013\u002D]/.test(t);
    }

    function isCursorInBlockquote(state) {
        var $from = state.selection.$from;
        for (var d = $from.depth; d > 0; d--) {
            if ($from.node(d).type.name === 'blockquote') {
                return true;
            }
        }
        return false;
    }

    /**
     * Добавить или обновить подпись в конце blockquote (как cite / второй абзац на сайте).
     * Пустое поле: если последний абзац похож на подпись (с тире) — удаляет его.
     */
    function applyQuoteAuthor(editor, authorRaw) {
        var state = editor.view.state;
        var schema = state.schema;
        var $from = state.selection.$from;
        var bqDepth = -1;
        for (var d = $from.depth; d > 0; d--) {
            if ($from.node(d).type.name === 'blockquote') {
                bqDepth = d;
                break;
            }
        }
        if (bqDepth < 0) {
            return false;
        }
        var bq = $from.node(bqDepth);
        var start = $from.before(bqDepth);
        var tr = state.tr;
        var normalized = normalizeAuthorLine(String(authorRaw || '').trim());
        var cc = bq.childCount;
        if (cc < 1) {
            return false;
        }
        var last = bq.child(cc - 1);
        var lastFrom = start + 1;
        for (var i = 0; i < cc - 1; i++) {
            lastFrom += bq.child(i).nodeSize;
        }
        var lastTo = lastFrom + last.nodeSize;

        if (!normalized) {
            if (isLikelyAttributionParagraph(last)) {
                tr.delete(lastFrom, lastTo);
                editor.view.dispatch(tr.scrollIntoView());
            }
            return true;
        }

        var textNode = schema.text(normalized);
        var newPara = schema.nodes.paragraph.create({}, [textNode]);

        if (isLikelyAttributionParagraph(last)) {
            tr.replaceWith(lastFrom, lastTo, newPara);
        } else {
            var insertPos = start + 1 + bq.content.size;
            tr.insert(insertPos, newPara);
        }
        editor.view.dispatch(tr.scrollIntoView());
        return true;
    }

    /**
     * Вне blockquote: панель с полем автора → вставка цитаты и подписи.
     * Внутри blockquote: повторное нажатие снимает оформление цитаты (как штатная кнопка).
     */
    function openBlockquoteAuthorModal(event, editor) {
        if (!editor) {
            return;
        }
        var state = editor.view.state;
        if (isCursorInBlockquote(state)) {
            try {
                editor.chain().focus().toggleBlockquote().run();
            } catch (err) {
                console.error('[zmRichEditor] toggle blockquote:', err);
            }
            return;
        }
        var anchor = { from: state.selection.from, to: state.selection.to };
        showPanel(
            event,
            editor,
            function (panel) {
                panel.appendChild(buildHeading('Цитата'));
                var authorId = 'zm-rte-bq-author-' + Math.random().toString(36).slice(2, 9);
                var fieldWrap = document.createElement('div');
                fieldWrap.className = 'zm-rte-bq-author-field';
                var lab = document.createElement('label');
                lab.className = 'zm-rte-bq-author-label';
                lab.setAttribute('for', authorId);
                lab.textContent = 'Автор цитаты';
                var inp = document.createElement('input');
                inp.type = 'text';
                inp.id = authorId;
                inp.className = 'zm-rte-bq-author-input';
                inp.setAttribute('placeholder', 'Необязательно, например: д-р Смирнова Е. В.');
                inp.setAttribute('autocomplete', 'off');
                fieldWrap.appendChild(lab);
                fieldWrap.appendChild(inp);

                var actions = document.createElement('div');
                actions.className = 'zm-rte-bq-author-actions';
                var submit = document.createElement('button');
                submit.type = 'button';
                submit.className = 'zm-rte-reset zm-rte-bq-author-submit';
                submit.textContent = 'Вставить цитату';
                var cancel = document.createElement('button');
                cancel.type = 'button';
                cancel.className = 'zm-rte-menu-item zm-rte-bq-author-cancel';
                cancel.textContent = 'Отмена';

                function commit() {
                    try {
                        var author = String(inp.value || '').trim();
                        editor
                            .chain()
                            .focus()
                            .setTextSelection({ from: anchor.from, to: anchor.to })
                            .toggleBlockquote()
                            .run();
                        if (author) {
                            applyQuoteAuthor(editor, author);
                        }
                    } catch (err) {
                        console.error('[zmRichEditor] blockquote modal:', err);
                    }
                    closeAll();
                }

                submit.addEventListener('click', function (e) {
                    e.preventDefault();
                    commit();
                });
                cancel.addEventListener('click', function (e) {
                    e.preventDefault();
                    closeAll();
                });
                inp.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        commit();
                    }
                });

                actions.appendChild(submit);
                actions.appendChild(cancel);
                panel.appendChild(fieldWrap);
                panel.appendChild(actions);
                setTimeout(function () {
                    try {
                        inp.focus();
                    } catch (_) {
                        /* no-op */
                    }
                }, 0);
            },
            { extraClass: 'zm-rte-panel--blockquote-modal' },
        );
    }

    window.zmRichEditor = {
        openTextColorPanel: openTextColorPanel,
        openHighlightPanel: openHighlightPanel,
        openLineSpacingPanel: openLineSpacingPanel,
        openBlockquoteAuthorModal: openBlockquoteAuthorModal,
        closeAll: closeAll,
    };
})();
