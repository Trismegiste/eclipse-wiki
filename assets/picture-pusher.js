/*
 * Behavior for broadcasting picture
 */
export default () => ({
        trigger: {
            ['x-on:click.prevent'] () {
                let url = this.$el.href
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json, text/plain, */*',
                        'Content-Type': 'application/json'
                    }
                }).then((response) => {
                    if (!response.ok) {
                        return Promise.reject(response)
                    }
                    return response.json()
                }).then((json) => {
                    pushFlash(this.$el, 'success', json.message)
                }).catch((error) => {
                    if (typeof error.json === "function") {
                        error.json().then((jsonError) => {
                            pushFlash(this.$el, 'error', jsonError.message)
                        }).catch((genericError) => {
                            pushFlash(this.$el, 'error', genericError.statusText);
                        })
                    } else {
                        pushFlash(this.$el, 'error', error)
                    }
                })
            }}
    })