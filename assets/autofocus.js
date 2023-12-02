/*
 *  default focus
 */

const found = document.querySelector('main form.pure-form [data-autofocus]')
if (found) {
    found.focus()
} else {
    // fallback
    const found = document.querySelector('main form.pure-form input[type=text]')
    if (found) {
        found.focus()
    }
}