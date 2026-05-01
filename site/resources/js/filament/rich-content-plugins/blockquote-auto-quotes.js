import Blockquote from '@tiptap/extension-blockquote';

function wrapWithGuillemets(text) {
    const t = String(text ?? '').trim();
    if (!t) {
        return t;
    }
    if (t.includes('\u00AB')) {
        return t;
    }
    return '\u00AB' + t + '\u00BB';
}

function isInBlockquote(state) {
    const $from = state.selection.$from;
    for (let d = $from.depth; d > 0; d--) {
        if ($from.node(d).type.name === 'blockquote') {
            return true;
        }
    }
    return false;
}

/**
 * Заменяет стандартный Blockquote: после включения цитаты оборачивает текст
 * первого непустого абзаца в «ёлочки», если их ещё нет (как на публичном сайте).
 */
export default Blockquote.extend({
    addCommands() {
        return {
            ...this.parent?.(),
            toggleBlockquote:
                () =>
                ({ chain, editor }) => {
                    const wasInBlockquote = isInBlockquote(editor.state);
                    if (!chain().focus().toggleWrap(this.name).run()) {
                        return false;
                    }
                    const after = editor.state;
                    if (!isInBlockquote(after)) {
                        return true;
                    }
                    if (wasInBlockquote) {
                        return true;
                    }

                    return chain()
                        .command(({ tr, state, dispatch }) => {
                            const $pos = state.selection.$from;
                            let dq = $pos.depth;
                            while (dq > 0 && $pos.node(dq).type.name !== 'blockquote') {
                                dq--;
                            }
                            if (dq === 0) {
                                return true;
                            }
                            const bq = $pos.node(dq);
                            const bqPos = $pos.before(dq);
                            let pos = bqPos + 1;

                            for (let i = 0; i < bq.childCount; i++) {
                                const child = bq.child(i);
                                if (child.type.name === 'paragraph' && child.textContent.trim()) {
                                    const plain = child.textContent;
                                    const wrapped = wrapWithGuillemets(plain);
                                    if (wrapped !== plain) {
                                        const innerFrom = pos + 1;
                                        const innerTo = pos + 1 + child.content.size;
                                        let marks = [];
                                        if (
                                            child.childCount === 1 &&
                                            child.firstChild?.isText
                                        ) {
                                            marks = child.firstChild.marks;
                                        } else {
                                            marks = state.storedMarks ?? $pos.marks();
                                        }
                                        tr.replaceWith(
                                            innerFrom,
                                            innerTo,
                                            state.schema.text(wrapped, marks),
                                        );
                                        if (dispatch) {
                                            dispatch(tr.scrollIntoView());
                                        }
                                        return true;
                                    }
                                    break;
                                }
                                pos += child.nodeSize;
                            }
                            return true;
                        })
                        .run();
                },
        };
    },
});
