/*
 * Behavior for broadcasting picture
 */
import Alpine from 'alpinejs'

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
                    Alpine.store('notif').push('success', json.message)
                }).catch((error) => {
                    if (typeof error.json === "function") {
                        error.json().then((jsonError) => {
                            Alpine.store('notif').push('error', jsonError.message)
                        }).catch((genericError) => {
                            Alpine.store('notif').push('error', genericError.statusText);
                        })
                    } else {
                        Alpine.store('notif').push('error', error)
                    }
                })
            }}
    })