/*
 * eclipse-wiki
 */

export class MercureClient {
    jwt = null

    constructor(jwt) {
        this.jwt = jwt
    }

    async publish(topic, type, content) {
        const resp = await fetch(window.location.origin + '/.well-known/mercure', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                Authorization: `Bearer ${this.jwt}`
            },
            body: new URLSearchParams({
                topic: topic,
                type: type,
                data: JSON.stringify(content)
            })
        })

        return await resp.text()
    }
}