/*
 * Behavior for broadcasting block quote
 */
import Alpine from 'alpinejs'

export default (urlPush) => ({
        pushPdf(event) {
            let content = JSON.parse(event.currentTarget.parentElement.dataset.mw)
            fetch(urlPush, {method: 'POST', body: new URLSearchParams({wikitext: content.body.extsrc})})
                    .then(resp => resp.json())
                    .then(json => {
                        Alpine.store('notif').push(json.level, json.message)
                    })
        }
    })