/*
 * Eclipse Wiki
 */
import { MercureClient } from 'mercure-client';

export default (jwt) => {
    document.querySelectorAll('[typeof="mw:Transclusion"] + i.icon-view3d[data-cell-index]').forEach(item => {
        item.classList.add('legend-highlighting')
        item.addEventListener('click', event => {
            event.preventDefault()
            event.target.classList.add('highlighting-action')
            const client = new MercureClient(jwt)
            client.publish('ping-position', 'indexed', {cell: event.target.dataset.cellIndex})
        })
        item.addEventListener("animationend", () => {
            item.classList.remove("highlighting-action")
        })
    })
}