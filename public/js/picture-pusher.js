/*
 * Scan all pushable picture and add an event listener for the click
 */

let pictures = document.querySelectorAll('.pushable a')
pictures.forEach(function (picture) {
    let url = picture.href
    picture.addEventListener('click', function (event) {
        // stop propagation
        event.preventDefault()
        // post image title for sending to external device
        fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json, text/plain, */*',
                'Content-Type': 'application/json'
            }
        }).then(function (response) {
            if (!response.ok) {
                return Promise.reject(response)
            }
            return response.json()
        }).then(function (json) {
            pushFlash(picture, 'success', json.message)
        }).catch(function (error) {
            if (typeof error.json === "function") {
                error.json().then(function (jsonError) {
                    pushFlash(picture, 'error', jsonError.message)
                }).catch(function (genericError) {
                    pushFlash(picture, 'error', genericError.statusText);
                })
            } else {
                pushFlash(picture, 'error', error)
            }
        })
    })
})