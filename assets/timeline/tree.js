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

    moveNodeDelta(sourceIdx, targetIdx, delta) {
        let flatten = this.getFlatList()
        // get model on list
        let source = flatten[sourceIdx]
        let target = flatten[targetIdx]
        // go back to internal model
        let sourceParent = source.parent
        let targetParent = target.parent
        let sourceNode = sourceParent.nodes[source.childIdx]
        let targetNode = targetParent.nodes[target.childIdx]
        // remove source
        let removed = sourceParent.nodes.splice(source.childIdx, 1)
        // find the new index of target since it could change
        let newTargetIdx = targetParent.nodes.indexOf(targetNode) + delta
        targetParent.nodes.splice(newTargetIdx, 0, removed[0])
    }

    moveNodeBefore(sourceIdx, targetIdx) {
        if (sourceIdx !== targetIdx) {
            this.moveNodeDelta(sourceIdx, targetIdx, 0)
        }
    }

    moveNodeAfter(sourceIdx, targetIdx) {
        if (sourceIdx !== targetIdx) {
            this.moveNodeDelta(sourceIdx, targetIdx, 1)
        }
    }

    appendNode(sourceIdx, targetIdx) {
        let flatten = this.getFlatList()
        // get model on list
        let source = flatten[sourceIdx]
        let target = flatten[targetIdx]
        // go back to internal model
        let sourceParent = source.parent
        let targetParent = target.parent
        let sourceNode = sourceParent.nodes[source.childIdx]
        let targetNode = targetParent.nodes[target.childIdx]
        // remove source
        let removed = source.parent.nodes.splice(source.childIdx, 1)
        targetNode.nodes.push(removed[0])
    }
}