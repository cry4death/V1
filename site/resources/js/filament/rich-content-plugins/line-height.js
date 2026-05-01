import { Extension } from '@tiptap/core'

const LineHeight = Extension.create({
    name: 'lineHeight',

    addOptions() {
        return {
            types: ['paragraph', 'heading'],
            defaultLineHeight: null,
        }
    },

    addGlobalAttributes() {
        return [
            {
                types: this.options.types,
                attributes: {
                    lineHeight: {
                        default: this.options.defaultLineHeight,
                        parseHTML: (element) => element.style?.lineHeight || null,
                        renderHTML: (attributes) => {
                            if (!attributes.lineHeight) {
                                return {}
                            }

                            return {
                                style: `line-height: ${attributes.lineHeight}`,
                            }
                        },
                    },
                },
            },
        ]
    },

    addCommands() {
        return {
            setLineHeight:
                (lineHeight) =>
                ({ commands }) => {
                    return this.options.types
                        .map((type) => commands.updateAttributes(type, { lineHeight }))
                        .some((result) => result)
                },
            unsetLineHeight:
                () =>
                ({ commands }) => {
                    return this.options.types
                        .map((type) => commands.resetAttributes(type, 'lineHeight'))
                        .some((result) => result)
                },
        }
    },
})

export default LineHeight
