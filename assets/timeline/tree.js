/*
 * eclipse-wiki
 */
export class TimelineTree {
    data = null
    nodes = []

    getFlatList() {
        let listing = []
        this.recursiveFlat(0, this, listing)

        return listing
    }

    recursiveFlat(level, node, listing) {
        listing.push({level, title: node.data.title, finished: node.data.finished})
        for (let child of node.nodes) {
            this.recursiveFlat(level + 1, child, listing)
        }
    }
}