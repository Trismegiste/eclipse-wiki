/*
 * Eclipse Wiki
 */

// We need to rely on this trick of subscribing and cannot use Alpine because Parsoid doesn't allow non-HTML attributes
// like x-data nor x-on:click. We cannot use a <a> neither because it will be escaped by Parsoid
export default (urlPush) => {
    document.querySelectorAll('i[data-pushable="pdf"]').forEach(item => {
        item.addEventListener('click', event => {
            event.preventDefault()
            let title = event.currentTarget.dataset.title
            fetch(urlPush, {method:'POST', body: new URLSearchParams({title})})
		.then(resp => resp.json())
                .then(json => {
                    Alpine.store('notif').push(json.level, json.message)
                })
        })
    })
}
