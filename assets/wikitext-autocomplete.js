/*
 * Eclipse Wiki
 */
import {position, offset} from 'caret-pos';
export default (strategy) => {
    return () => ({
            caretChangingKey: ['Escape', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowUp', 'ArrowDown', 'Home', 'End', 'PageUp', 'PageDown'],
            content: '',
            open: false,
            caretPos: {top: 0, left: 0},
            result: [],
            selected: null,
            strategy: strategy,

            init() {
                this.content = this.$refs.editor.value.trim()
            },

            editKeyUp(event) {
                // if the caret is changing position : stop the autocomplete
                if (this.caretChangingKey.includes(event.key)) {
                    this.open = false
                    return;
                }

                if (this.open && (event.key === 'Enter')) {
                    this.choose()
                    return;
                }

                // iterator over strategies
                let found = false
                for (let strategy of this.strategy) {
                    let extract = strategy.pattern.exec(this.content.substring(0, this.getCaretPosition()))
                    if (null !== extract) {
                        found = true
                        // position the combobox if we match
                        if (!this.open) {
                            let caretPosition = offset(this.$refs.editor)
                            this.caretPos.top = (caretPosition.top + caretPosition.height - this.$refs.editor.scrollTop) + 'px'
                            this.caretPos.left = caretPosition.left + 'px'
                        }
                        //fetching ajax
                        fetch(strategy.url + extract[1])
                                .then((response) => {
                                    return response.json()
                                })
                                .then((data) => {
                                    this.result = []
                                    for (let entry of data) {
                                        let attr = strategy.boundAttr(entry)
                                        attr['value'] = entry
                                        this.result.push(attr)
                                    }
                                    // showing the combobox only if there are results
                                    if (data.length > 0) {
                                        this.selected = data[0]
                                        this.open = true
                                    } else {
                                        this.selected = null
                                        this.open = false
                                    }
                                })
                        break
                    }
                }
                if (!found) {
                    this.open = false
                }
            },

            choose(event) {
                let caret = this.getCaretPosition()
                let before = this.content.substring(0, caret)
                let after = this.content.substring(caret)

                for (let strategy of this.strategy) {
                    let pattern = strategy.pattern
                    let extract = pattern.exec(before)
                    if (null !== extract) {
                        let newValue = strategy.replace(this.selected) + ' '
                        caret += newValue.length - extract[0].length
                        this.content = pattern[Symbol.replace](before, newValue) + after
                        this.$nextTick(() => {
                            this.$refs.editor.focus()
                            this.$refs.editor.setSelectionRange(caret, caret)
                        })
                        break
                    }
                }
                this.open = false
            },

            getCaretPosition() {
                return this.$refs.editor.selectionEnd
            },

            comboKeyUp(event) {
                switch (event.key) {
                    case 'Enter':
                        this.choose(event)
                        break
                    case 'Escape':
                        let caret = this.getCaretPosition()
                        this.$refs.editor.focus()
                        this.$refs.editor.setSelectionRange(caret, caret)
                        this.open = false
                        break
                }
            }
        })
}