/*
 * Eclipse Wiki
 */
import { Ollama } from 'ollama/browser';
export default (url, payload) => ({
        content: '',
        waiting: false,

        async init() {
            if (payload === null) {
                return;
            }

            this.waiting = true

            try {
                const ollama = new Ollama({host: url})
                const response = await ollama.chat(payload)
                for await (const part of response) {
                    this.waiting = false
                    this.content = this.content.concat(part.message.content)
                }

                this.postProcessing()
            } catch (ex) {
                console.error(ex)
                Alpine.store('notif').push('error', 'Ollama is unreachable : ' + ex.message)
                this.waiting = false
                return;
            }
        },

        /** override this method if you want to extend the behavior of this factory after the content generation */
        postProcessing: function () {}
    })
