/*
 * Eclipse Wiki
 */

export default (source, url) => ({
        source: source,
        url: url,
        waiting: false,
        gallery: [],

        async search(event) {
            this.gallery = []
            this.waiting = true
            try {
                const resp = await fetch(this.url + '?q=' + event.detail.query)
                if (!resp.ok) {
                    throw new Error(this.source, {cause: await resp.json()})
                }
                this.gallery = await resp.json()
            } catch (error) {
                const flash = error.cause
                Alpine.store('notif').push(flash.level, flash.message)
            } finally {
                this.waiting = false
            }
        },

        linkParameter(picture) {
            return {
                ['href']: picture.full,
                ['title']: picture.prompt,
                ['target']: '_blank'
            }
        }
    })