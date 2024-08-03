/*
 * Eclipse Wiki
 */

export default (url, payloadId) => ({
        payload: JSON.parse(document.getElementById(payloadId).textContent),
        content: '',
        waiting: false,

        async init() {
            const decoder = new TextDecoder()

            if (this.payload === null) {
                return;
            }

            this.waiting = true

            try {
                const resp = await fetch(url, {
                    method: 'POST',
                    body: JSON.stringify(this.payload)
                })

                const reader = await resp.body.getReader()
                for (; ; ) {
                    let {value: chunk, done: readerDone} = await reader.read()
                    this.waiting = false
                    if (readerDone) {
                        break
                    }
                    const msg = JSON.parse(decoder.decode(chunk))
                    this.content = this.content.concat(msg.message.content)
                }
                this.postProcessing()
            } catch (ex) {
                Alpine.store('notif').push('error', 'Ollama is unreachable : ' + ex.message)
                this.waiting = false
                return;
            }
        },

        postProcessing: function () {}
    })
