/*
 * Behavior for broadcasting block quote
 * This is a parameterized factory
 */
import Alpine from 'alpinejs'

export default (urlPush) => {
    return () => ({
            url: urlPush,

            pushPdf(event) {
                let content = JSON.parse(event.currentTarget.parentElement.dataset.mw)
                fetch(this.url, {method: 'POST', body: new URLSearchParams({wikitext: content.body.extsrc})})
                        .then(resp => resp.json())
                        .then(json => {
                            Alpine.store('notif').push(json.level, json.message)
                        })
            }
        })
}