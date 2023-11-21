/*
 * eclipse-wiki
 */
export class TimelineTree {
    data = null
    nodes = []

    getFlatList() {
        let listing = []
        this.recursiveFlat(0, this, listing, null, null)

        return listing
    }

    recursiveFlat(level, node, listing, parent, childIdx) {
        listing.push({level, title: node.data.title, finished: node.data.finished, parent, childIdx})
        for (const [idx, child] of node.nodes.entries()) {
            this.recursiveFlat(level + 1, child, listing, node, idx)
        }
    }

    moveNodeAfter(sourceIdx, targetIdx) {
        let flatten = this.getFlatList()
        let source = flatten[sourceIdx]
        let target = flatten[targetIdx]
        let removed = source.parent.nodes.splice(source.childIdx, 1)
        target.parent.nodes.splice(target.childIdx, 0, removed[0])
    }

    appendNode(sourceIdx, targetIdx) {
        let flatten = this.getFlatList()
        let source = flatten[sourceIdx]
        let target = flatten[targetIdx]
        let removed = source.parent.nodes.splice(source.childIdx, 1)
        target.parent.nodes[target.childIdx].nodes.push(removed[0])
    }
}