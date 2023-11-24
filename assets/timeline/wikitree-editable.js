/*
 * eclipse-wiki
 */
import { TimelineTree } from 'timeline-tree';

export default () => ({
        tree: null,
        editingNode: null,

        init() {
            this.tree = Object.assign(new TimelineTree(), JSON.parse(this.$refs.wikitree.value))
        },

        nodeDragStart(event) {
            event.dataTransfer.setData("text/plain", this.$el.dataset.index)
            event.dataTransfer.effectAllowed = "move";
            event.target.classList.add("dragging")
        },

        nodeDragEnd(event) {
            event.target.classList.remove("dragging")
        },

        dropZoneEnter(event) {
            event.preventDefault()
            event.target.classList.add("dropzone-hilite")
        },

        dropZoneLeave(event) {
            event.preventDefault()
            event.target.classList.remove("dropzone-hilite")
        },

        nodeEnter(event) {
            event.preventDefault()
            event.target.classList.add("parent-hilite")
        },

        nodeLeave(event) {
            event.preventDefault()
            event.target.classList.remove("parent-hilite")
        },

        dropZoneOver(event) {
            event.preventDefault()
        },

        moveNodeBefore(event) {
            event.preventDefault()
            let source = event.dataTransfer.getData("text")
            let target = this.$el.dataset.index
            this.tree.moveNodeBefore(parseInt(source), parseInt(target))
            event.target.classList.remove("dropzone-hilite")
        },

        moveNodeAfter(event) {
            event.preventDefault()
            let source = event.dataTransfer.getData("text")
            let target = this.$el.dataset.index
            this.tree.moveNodeAfter(parseInt(source), parseInt(target))
            event.target.classList.remove("dropzone-hilite")
        },

        appendNode(event) {
            event.preventDefault()
            let source = event.dataTransfer.getData("text")
            let target = this.$el.dataset.index
            this.tree.appendNode(parseInt(source), parseInt(target))
            event.target.classList.remove("parent-hilite")
        },

        checkNode(event) {
            let source = this.$el.dataset.index
            this.tree.flipNode(parseInt(source))
        },

        appendNewChild(event) {
            let source = this.$el.dataset.index
            this.tree.appendNewChild(parseInt(source), 'New child')
        },

        deleteNode(event) {
            let state = this.$el.dataset
            if (state.alive === 'true') {
                this.tree.deleteNode(parseInt(state.index))
            } else {
                document.querySelectorAll('button[data-alive=true]').forEach(elem => {
                    elem.dataset.alive = 'false'
                })
                state.alive = 'true'
            }
        },

        editNode(event) {
            let source = this.$el.dataset.index
            this.editingNode = parseInt(source)
            let flatten = this.tree.getFlatList()
            this.content = flatten[this.editingNode].node.data.title
            this.$nextTick(() => {
                this.$refs.editor.focus()
            })
        },

        confirmEditNode(event) {
            let flatten = this.tree.getFlatList()
            flatten[this.editingNode].node.data.title = this.content.replaceAll(/\n|\r|\s{2,}/g, ' ').trim()
            this.editingNode = null
        },

        cancelEditNode(event) {
            this.editingNode = null
        },

        dumpTree(event) {
            this.$refs.wikitree.value = JSON.stringify(this.tree)
        }

    })