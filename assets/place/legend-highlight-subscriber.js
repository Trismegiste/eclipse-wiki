/*
 * Eclipse Wiki
 */

export default (urlFeedback) => {
    document.querySelectorAll('[typeof="mw:Transclusion"] + i.icon-view3d[data-cell-index]').forEach(item => {
        item.classList.add('legend-highlighting')
        item.addEventListener('click', event => {
            event.preventDefault()
            event.target.classList.add('highlighting-action')
            fetch(urlFeedback, {
                method: 'POST',
                body: JSON.stringify({cell: event.target.dataset.cellIndex})
            })
        })
        item.addEventListener("animationend", () => {
            item.classList.remove("highlighting-action")
        })
    })
}