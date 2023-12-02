/*
 * Eclipse Wiki
 */

export default (urlFeedback) => {
    document.querySelectorAll('[typeof="mw:Transclusion"] + i.icon-view3d[data-cell-index]').forEach(item => {
        item.classList.add('legend-highlighting')
        item.addEventListener('click', event => {
            event.preventDefault()
            event.target.classList.add('highlighting-action')
            const feedbackSocket = new WebSocket(urlFeedback)
            feedbackSocket.onopen = () => {
                feedbackSocket.send(JSON.stringify({mode: 'indexed', cell: event.target.dataset.cellIndex}))
                feedbackSocket.close()
            }
        })
        item.addEventListener("animationend", () => {
            item.classList.remove("highlighting-action")
        })
    })
}