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
        console.log(node)
        listing.push({level, title: node.data.title, finished: node.data.finished, parent, childIdx})
        for (const [idx, child] of node.nodes.entries()) {
            this.recursiveFlat(level + 1, child, listing, node, idx)
        }
    }
}