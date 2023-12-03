/*
 * eclipse-wiki
 */
export default () => ({
        detail: '',

        selectUpdate(event) {
            const param = event.currentTarget.dataset
            this.detail = '<i class="icon-spin3 animate-spin"></i>'
            fetch(param.url + '?key=' + event.target.value)
                    .then(resp => {
                        return resp.text()
                    })
                    .then(data => {
                        this.detail = data
                    })
        }
    })